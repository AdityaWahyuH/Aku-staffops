<?php
namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\ScheduleAssignment;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\CarbonImmutable;
use App\Services\SchedulingService;
use Illuminate\Validation\Rule;

class AvailabilityController extends Controller
{
    public function index(Request $request, SchedulingService $scheduler)
    {
        $date = $request->validate(['date'=>['nullable','date_format:Y-m-d']])['date'] ?? today()->toDateString();
        $staff = Staff::where('is_active',true)->orderBy('name')->get();
        $availability = Availability::whereDate('date',$date)->get()->keyBy('staff_id');
        $dayStart = CarbonImmutable::parse($date)->startOfDay();
        $dayEnd = $dayStart->addDay();
        $booked = ScheduleAssignment::with(['shift','location','staff'])
            ->whereBetween('schedule_date', [$dayStart->subDay()->toDateString(), $date])
            ->whereIn('status',['draft','published'])->get()
            ->filter(function ($item) use ($dayStart,$dayEnd,$scheduler) {
                [$start,$end] = $scheduler->bounds($item->schedule_date->toDateString(), $item->shift->start_time, $item->shift->end_time);
                return $start->lessThan($dayEnd) && $dayStart->lessThan($end);
            })->groupBy('staff_id');
        return view('availability.index',compact('date','staff','availability','booked'));
    }
    public function update(Request $request)
    {
        $data = $request->validate([
            'date'=>['required','date_format:Y-m-d'], 'records'=>['required','array'],
            'records.*.status'=>['required',Rule::in(['available','leave','unavailable','unknown'])],
            'records.*.notes'=>['nullable','string','max:255'],
        ]);
        $allowed = Staff::where('is_active',true)->pluck('id')->map(fn ($id) => (int) $id)->all();
        DB::transaction(function () use ($data, $allowed, $request) {
            foreach ($data['records'] as $id => $row) {
                if (!in_array((int) $id,$allowed,true)) continue;
                if ($row['status'] === 'unknown') {
                    Availability::where('staff_id',$id)->whereDate('date',$data['date'])->delete();
                } else {
                    Availability::updateOrCreate(['staff_id'=>$id,'date'=>$data['date']], [
                        'status'=>$row['status'], 'notes'=>$row['notes'] ?? null, 'updated_by'=>$request->user()->id,
                    ]);
                }
            }
        });
        return redirect()->route('availability.index',['date'=>$data['date']])
            ->with('success','Ketersediaan harian diperbarui. Jadwal lama perlu ditinjau jika ada perubahan mendadak.');
    }
}
