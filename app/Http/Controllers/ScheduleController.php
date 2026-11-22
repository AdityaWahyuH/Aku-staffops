<?php
namespace App\Http\Controllers;

use App\Models\ScheduleAssignment;
use App\Models\ScheduleBatch;
use App\Models\Staff;
use App\Models\WorkLocation;
use App\Services\SchedulingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ScheduleController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'date'=>['nullable','date_format:Y-m-d'],
            'status'=>['nullable',Rule::in(['draft','published','cancelled'])],
            'location_id'=>['nullable','integer','exists:work_locations,id'],
            'q'=>['nullable','string','max:150'],
        ]);
        $date = $filters['date'] ?? today()->toDateString();
        $query = ScheduleAssignment::with(['staff','location','shift'])
            ->whereDate('schedule_date', $date)
            ->when($filters['status'] ?? null, fn ($q,$status) => $q->where('status',$status))
            ->when($filters['location_id'] ?? null, fn ($q,$id) => $q->where('location_id',$id))
            ->when($filters['q'] ?? null, fn ($q,$value) => $q->whereHas('staff',fn ($s) => $s->where('name','like',"%{$value}%")->orWhere('code','like',"%{$value}%")));
        $assignments = $query->orderBy('location_id')->orderBy('shift_id')->orderBy('staff_id')->paginate(25)->withQueryString();
        return view('schedules.index', [
            'date'=>$date, 'filters'=>$filters, 'assignments'=>$assignments,
            'locations'=>WorkLocation::where('is_active',true)->orderBy('name')->get(),
            'staff'=>Staff::where('is_active',true)->orderBy('name')->get(),
            'lastBatch'=>ScheduleBatch::whereDate('schedule_date',$date)->latest('id')->first(),
        ]);
    }
    public function generate(Request $request, SchedulingService $scheduler)
    {
        $data = $request->validate([
            'date'=>['required','date_format:Y-m-d'],
            'location_id'=>['nullable','integer','exists:work_locations,id'],
        ]);
        $batch = $scheduler->generate($data['date'], $data['location_id'] ?? null, $request->user()->id);
        return redirect()->route('schedules.index',['date'=>$data['date']])
            ->with('success',"Generate selesai: {$batch->created_count} draft baru. Kekurangan {$batch->shortage_count} posisi. Proses tidak dihentikan meski staf kurang.");
    }
    public function update(Request $request, ScheduleAssignment $assignment, SchedulingService $scheduler)
    {
        if ($assignment->status !== 'draft') {
            throw ValidationException::withMessages(['assignments'=>'Hanya penugasan draft yang dapat diubah.']);
        }
        $data = $request->validate(['staff_id'=>['required','integer','exists:staff,id']]);
        DB::transaction(function () use ($assignment,$data,$scheduler) {
            Staff::whereIn('id',[$assignment->staff_id,$data['staff_id']])->orderBy('id')->lockForUpdate()->get();
            $assignment->refresh();
            if ($assignment->status !== 'draft') {
                throw ValidationException::withMessages(['assignments'=>'Status draft berubah. Muat ulang halaman.']);
            }
            $assignment->staff_id = $data['staff_id'];
            [$busy,$available] = $scheduler->scheduleContext($assignment->schedule_date->toDateString(),true);
            $scheduler->verifyAssignment($assignment,$busy,$available);
            $assignment->save();
        });
        return back()->with('success','Penugasan draft diperbarui tanpa benturan jadwal.');
    }
    public function publishSelected(Request $request, SchedulingService $scheduler)
    {
        $data = $request->validate([
            'assignment_ids'=>['required','array','min:1','max:1000'],
            'assignment_ids.*'=>['required','integer','distinct','exists:schedule_assignments,id'],
        ]);
        $count = $this->publishIds($data['assignment_ids'], $request, $scheduler);
        return back()->with('success',"{$count} jadwal berhasil dipublikasikan sekaligus.");
    }
    public function publishDay(Request $request, SchedulingService $scheduler)
    {
        $data = $request->validate(['date'=>['required','date_format:Y-m-d']]);
        $ids = ScheduleAssignment::whereDate('schedule_date',$data['date'])
            ->where('status','draft')->orderBy('id')->pluck('id')->all();
        if (!$ids) {
            return back()->with('success','Tidak ada draft pada tanggal tersebut yang perlu dipublikasikan.');
        }
        $count = $this->publishIds($ids, $request, $scheduler);
        return back()->with('success',"{$count} draft pada {$data['date']} telah dipublikasikan sekaligus.");
    }
    private function publishIds(array $ids, Request $request, SchedulingService $scheduler): int
    {
        return DB::transaction(function () use ($ids,$request,$scheduler) {
            $requestedItems = ScheduleAssignment::whereIn('id',$ids)->get();
            // Kunci staf SEBELUM assignment, sama dengan urutan generate untuk mengurangi deadlock.
            Staff::whereIn('id',$requestedItems->pluck('staff_id')->unique())->orderBy('id')->lockForUpdate()->get();
            $items = ScheduleAssignment::with(['staff','shift'])->whereIn('id',$ids)->orderBy('id')->lockForUpdate()->get();
            if ($items->count() !== count($ids) || $items->contains(fn ($a) => $a->status !== 'draft')) {
                throw ValidationException::withMessages(['assignment_ids'=>'Hanya jadwal draft yang boleh dipublikasikan.']);
            }
            $contexts = [];
            foreach ($items as $item) {
                $date = $item->schedule_date->toDateString();
                if (!isset($contexts[$date])) $contexts[$date] = $scheduler->scheduleContext($date,true);
                [$busy,$availability] = $contexts[$date];
                $scheduler->verifyAssignment($item,$busy,$availability);
            }
            foreach ($items as $item) {
                $item->update(['status'=>'published','published_at'=>now(),'published_by'=>$request->user()->id]);
            }
            return $items->count();
        }, 3);
    }
    public function cancel(ScheduleAssignment $assignment)
    {
        if ($assignment->status === 'cancelled') return back()->with('success','Penugasan sudah dibatalkan.');
        $assignment->update(['status'=>'cancelled']);
        return back()->with('success','Penugasan dibatalkan dan riwayat tetap disimpan.');
    }
    private function exportItems(Request $request)
    {
        $data = $request->validate(['date'=>['required','date_format:Y-m-d']]);
        return [$data['date'], ScheduleAssignment::with(['staff','location','shift'])
            ->whereDate('schedule_date',$data['date'])->where('status','published')
            ->get()->sortBy(fn ($x) => $x->location->name.'|'.$x->shift->start_time.'|'.$x->staff->name)->values()];
    }
    public function print(Request $request)
    {
        [$date,$items] = $this->exportItems($request);
        $byLocation = $items->groupBy(fn ($x) => $x->location->name);
        return view('schedules.print',compact('date','items','byLocation'));
    }
    public function csv(Request $request): StreamedResponse
    {
        [$date,$items] = $this->exportItems($request);
        return response()->streamDownload(function () use ($items) {
            $out = fopen('php://output','w'); fwrite($out,"\xEF\xBB\xBF");
            fputcsv($out,['Tanggal','Nama Staf','Kode Staf','Lokasi Kerja','Shift','Mulai','Selesai','Status']);
            foreach ($items as $item) {
                fputcsv($out,[$item->schedule_date->format('Y-m-d'),$item->staff->name,$item->staff->code,
                    $item->location->name,$item->shift->name,substr($item->shift->start_time,0,5),substr($item->shift->end_time,0,5),'Dipublikasikan']);
            }
            fclose($out);
        }, "jadwal-publikasi-{$date}.csv", ['Content-Type'=>'text/csv; charset=UTF-8']);
    }
}
