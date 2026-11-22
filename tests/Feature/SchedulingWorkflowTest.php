<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\ScheduleAssignment;
use App\Models\Shift;
use App\Models\ShiftRequirement;
use App\Models\Staff;
use App\Models\User;
use App\Models\WorkLocation;
use App\Services\SchedulingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SchedulingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function prepareSample(int $staffCount, int $required): array
    {
        $admin = User::create(['name'=>'Admin Pengujian','email'=>'admin-test@example.test','password'=>'DummyTest123!','role'=>'admin']);
        $location = WorkLocation::create(['name'=>'Gedung A','is_active'=>true]);
        $shift = Shift::create(['name'=>'Pagi','start_time'=>'07:00','end_time'=>'15:00','is_active'=>true]);
        ShiftRequirement::create(['location_id'=>$location->id,'shift_id'=>$shift->id,'required_count'=>$required]);
        $date = '2026-12-14';
        for ($i=1;$i<=$staffCount;$i++) {
            $person = Staff::create(['code'=>"UJI-{$i}",'name'=>"Staf {$i}",'is_active'=>true]);
            Availability::create(['staff_id'=>$person->id,'date'=>$date,'status'=>'available','updated_by'=>$admin->id]);
        }
        return [$admin,$location,$shift,$date];
    }

    public function test_generate_keeps_partial_results_and_reports_staff_shortage(): void
    {
        [$admin,$location,$shift,$date] = $this->prepareSample(2,3);
        $batch = app(SchedulingService::class)->generate($date, $location->id, $admin->id);
        $this->assertSame(2, $batch->created_count);
        $this->assertSame(1, $batch->shortage_count);
        $this->assertDatabaseCount('schedule_assignments',2);
        $this->assertSame(2, ScheduleAssignment::where('status','draft')->count());
    }

    public function test_repeated_generate_does_not_duplicate_existing_assignments(): void
    {
        [$admin,$location,$shift,$date] = $this->prepareSample(3,2);
        $scheduler = app(SchedulingService::class);
        $scheduler->generate($date,$location->id,$admin->id);
        $secondBatch = $scheduler->generate($date,$location->id,$admin->id);
        $this->assertSame(0,$secondBatch->created_count);
        $this->assertSame(0,$secondBatch->shortage_count);
        $this->assertDatabaseCount('schedule_assignments',2);
    }

    public function test_overlap_detection_handles_overlapping_and_adjacent_shifts(): void
    {
        $scheduler = app(SchedulingService::class);
        $morning = (object)['start_time'=>'07:00','end_time'=>'15:00'];
        $other = (object)['start_time'=>'12:00','end_time'=>'20:00'];
        $evening = (object)['start_time'=>'15:00','end_time'=>'23:00'];
        $night = (object)['start_time'=>'23:00','end_time'=>'07:00'];
        $nextMorning = (object)['start_time'=>'06:00','end_time'=>'14:00'];
        $this->assertTrue($scheduler->overlaps('2026-12-14',$morning,'2026-12-14',$other));
        $this->assertFalse($scheduler->overlaps('2026-12-14',$morning,'2026-12-14',$evening));
        $this->assertTrue($scheduler->overlaps('2026-12-14',$night,'2026-12-15',$nextMorning));
    }

    public function test_staff_cannot_access_admin_dashboard(): void
    {
        $user = User::create(['name'=>'Staf Demo','email'=>'staff-test@example.test','password'=>'DummyTest123!','role'=>'staff']);
        $this->actingAs($user)->get(route('dashboard'))->assertForbidden();
        $this->actingAs($user)->get(route('staff-portal.index'))->assertOk();
    }

    public function test_mass_publication_of_all_drafts_for_a_date(): void
    {
        [$admin,$location,$shift,$date] = $this->prepareSample(2,2);
        app(SchedulingService::class)->generate($date,$location->id,$admin->id);
        $this->actingAs($admin)->post(route('schedules.publish-day'), ['date'=>$date])->assertSessionHasNoErrors();
        $this->assertSame(2,ScheduleAssignment::where('status','published')->count());
    }

    public function test_mass_publication_of_selected_assignments(): void
    {
        [$admin,$location,$shift,$date] = $this->prepareSample(2,2);
        app(SchedulingService::class)->generate($date,$location->id,$admin->id);
        $ids = ScheduleAssignment::pluck('id')->all();
        $this->actingAs($admin)->post(route('schedules.publish'), ['assignment_ids'=>$ids])->assertSessionHasNoErrors();
        $this->assertSame(2,ScheduleAssignment::where('status','published')->count());
    }
}
