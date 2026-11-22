<?php
namespace App\Http\Controllers;

use App\Models\ScheduleAssignment;
use Illuminate\Http\Request;

class StaffPortalController extends Controller
{
    public function index(Request $request)
    {
        $query = ScheduleAssignment::with(['location','shift'])
            ->where('staff_id',$request->user()->staff_id)->where('status','published')
            ->whereDate('schedule_date','>=',today()->subDays(7)->toDateString());
        return view('staff-portal.index', ['assignments'=>$query->orderBy('schedule_date')->paginate(20)]);
    }
}
