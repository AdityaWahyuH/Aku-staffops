<?php
namespace App\Http\Controllers;

use App\Models\ScheduleAssignment;
use App\Models\ScheduleBatch;
use App\Models\WorkLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    private function filters(Request $request): array
    {
        return $request->validate([
            'from'=>['nullable','date_format:Y-m-d'], 'to'=>['nullable','date_format:Y-m-d','after_or_equal:from'],
            'q'=>['nullable','string','max:150'], 'location_id'=>['nullable','integer','exists:work_locations,id'],
            'status'=>['nullable',Rule::in(['draft','published'])],
        ]);
    }
    private function query(array $filters)
    {
        return DB::table('schedule_assignments as a')
            ->join('staff as s','s.id','=','a.staff_id')
            ->join('work_locations as l','l.id','=','a.location_id')
            ->whereIn('a.status',['draft','published'])
            ->when($filters['from'] ?? null, fn ($q,$date) => $q->whereDate('a.schedule_date','>=',$date))
            ->when($filters['to'] ?? null, fn ($q,$date) => $q->whereDate('a.schedule_date','<=',$date))
            ->when($filters['location_id'] ?? null, fn ($q,$id) => $q->where('a.location_id',$id))
            ->when($filters['status'] ?? null, fn ($q,$status) => $q->where('a.status',$status))
            ->when($filters['q'] ?? null,fn ($q,$term) => $q->where(fn ($x) => $x->where('s.name','like',"%{$term}%")->orWhere('s.code','like',"%{$term}%")));
    }
    public function index(Request $request)
    {
        $filters = $this->filters($request);
        $query = $this->query($filters);
        $totals = (clone $query)->selectRaw("COUNT(*) AS total, SUM(CASE WHEN a.status='published' THEN 1 ELSE 0 END) AS published, SUM(CASE WHEN a.status='draft' THEN 1 ELSE 0 END) AS drafts")->first();
        $distribution = (clone $query)->select('s.id','s.code','s.name')
            ->selectRaw('COUNT(a.id) AS total')
            ->selectRaw("SUM(CASE WHEN a.status='published' THEN 1 ELSE 0 END) AS published")
            ->groupBy('s.id','s.code','s.name')->orderBy('s.name')->paginate(15)->withQueryString();
        $locations = WorkLocation::orderBy('name')->get();
        $shortageHistory = ScheduleBatch::with('location')->where('shortage_count','>',0)
            ->when($filters['from'] ?? null, fn ($q,$d) => $q->whereDate('schedule_date','>=',$d))
            ->when($filters['to'] ?? null, fn ($q,$d) => $q->whereDate('schedule_date','<=',$d))
            ->when($filters['location_id'] ?? null, fn ($q,$id) => $q->where('location_id',$id))
            ->latest('schedule_date')->limit(5)->get();
        return view('reports.index',compact('filters','totals','distribution','locations','shortageHistory'));
    }
    public function print(Request $request)
    {
        $filters = $this->filters($request);
        $rows = $this->query($filters)->select('s.code','s.name')
            ->selectRaw('COUNT(a.id) AS total')
            ->selectRaw("SUM(CASE WHEN a.status='published' THEN 1 ELSE 0 END) AS published")
            ->groupBy('s.code','s.name')->orderBy('s.name')->get();
        return view('reports.print',compact('filters','rows'));
    }
    public function csv(Request $request): StreamedResponse
    {
        $filters = $this->filters($request);
        $rows = $this->query($filters)->select('s.code','s.name','a.schedule_date','l.name as location_name','a.status')
            ->join('shifts as sh','sh.id','=','a.shift_id')
            ->addSelect('sh.name as shift_name','sh.start_time','sh.end_time')
            ->orderBy('a.schedule_date')->orderBy('s.name')->cursor();
        return response()->streamDownload(function () use ($rows) {
            $out = fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['Kode','Nama Staf','Tanggal','Lokasi','Shift','Mulai','Selesai','Status']);
            foreach ($rows as $r) fputcsv($out,[$r->code,$r->name,$r->schedule_date,$r->location_name,$r->shift_name,$r->start_time,$r->end_time,$r->status]);
            fclose($out);
        },'laporan-penjadwalan.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
