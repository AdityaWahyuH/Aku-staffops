<?php
namespace App\Http\Controllers;

use App\Models\Availability;
use App\Models\ScheduleAssignment;
use App\Models\ShiftRequirement;
use App\Models\Staff;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $date = today()->toDateString();
        $assignments = ScheduleAssignment::with(['staff','location','shift'])
            ->whereDate('schedule_date', $date)->whereIn('status',['draft','published'])->get();
        return view('dashboard.index', [
            'date' => $date, 'staffCount' => Staff::where('is_active',true)->count(),
            'availableCount' => Availability::whereDate('date',$date)->where('status','available')->count(),
            'assignmentCount' => $assignments->count(),
            'draftCount' => $assignments->where('status','draft')->count(),
            'requiredCount' => ShiftRequirement::where('required_count','>',0)->sum('required_count'),
            'assignments' => $assignments->take(8),
        ]);
    }
}
