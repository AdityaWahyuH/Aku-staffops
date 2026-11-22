<?php
namespace App\Http\Controllers;

use App\Models\ScheduleAssignment;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ShiftController extends Controller
{
    public function index() { return view('shifts.index', ['shifts'=>Shift::orderBy('start_time')->get()]); }
    public function create() { return view('shifts.form', ['shift'=>new Shift()]); }
    public function store(Request $request)
    {
        Shift::create($this->validated($request));
        return redirect()->route('shifts.index')->with('success','Shift ditambahkan.');
    }
    public function edit(Shift $shift) { return view('shifts.form',compact('shift')); }
    public function update(Request $request, Shift $shift)
    {
        $data = $this->validated($request,$shift);
        if (ScheduleAssignment::where('shift_id',$shift->id)->whereIn('status',['draft','published'])->exists()
            && (substr($shift->start_time,0,5) !== $data['start_time'] || substr($shift->end_time,0,5) !== $data['end_time'])) {
            throw ValidationException::withMessages(['start_time'=>'Jam shift yang telah memiliki jadwal aktif tidak boleh diubah.']);
        }
        $shift->update($data);
        return redirect()->route('shifts.index')->with('success','Shift diperbarui.');
    }
    private function validated(Request $request, ?Shift $shift = null): array
    {
        return $request->validate([
            'name'=>['required','string','max:60',Rule::unique('shifts','name')->ignore($shift?->id)],
            'start_time'=>['required','date_format:H:i','different:end_time'],
            'end_time'=>['required','date_format:H:i','different:start_time'],
            'is_active'=>['required','boolean'],
        ]);
    }
}
