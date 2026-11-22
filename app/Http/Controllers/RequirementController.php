<?php
namespace App\Http\Controllers;

use App\Models\Shift;
use App\Models\ShiftRequirement;
use App\Models\WorkLocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RequirementController extends Controller
{
    public function index()
    {
        return view('requirements.index', [
            'locations' => WorkLocation::where('is_active',true)->orderBy('name')->get(),
            'shifts' => Shift::where('is_active',true)->orderBy('start_time')->get(),
            'values' => ShiftRequirement::get()->mapWithKeys(fn ($x) => ["{$x->location_id}-{$x->shift_id}" => $x->required_count]),
        ]);
    }
    public function update(Request $request)
    {
        $data = $request->validate(['needs'=>['required','array'], 'needs.*'=>['required','integer','min:0','max:1000']]);
        $allowed = [];
        foreach (WorkLocation::where('is_active',true)->pluck('id') as $locationId) {
            foreach (Shift::where('is_active',true)->pluck('id') as $shiftId) {
                $allowed["{$locationId}-{$shiftId}"] = [$locationId,$shiftId];
            }
        }
        DB::transaction(function () use ($data, $allowed) {
            foreach ($data['needs'] as $key => $count) {
                if (!isset($allowed[$key])) continue;
                [$locationId,$shiftId] = $allowed[$key];
                ShiftRequirement::updateOrCreate(['location_id'=>$locationId,'shift_id'=>$shiftId], ['required_count'=>$count]);
            }
        });
        return back()->with('success','Kebutuhan personel berhasil disimpan.');
    }
}
