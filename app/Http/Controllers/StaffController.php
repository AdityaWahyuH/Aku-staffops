<?php
namespace App\Http\Controllers;

use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->query('q', ''));
        $staff = Staff::when($q, fn ($query) => $query->where(fn ($x) => $x->where('name','like',"%{$q}%")
                ->orWhere('code','like',"%{$q}%")))
            ->orderBy('name')->paginate(15)->withQueryString();
        return view('staff.index', compact('staff','q'));
    }
    public function create() { return view('staff.form', ['staff'=>new Staff()]); }
    public function store(Request $request)
    {
        Staff::create($this->validated($request));
        return redirect()->route('staff.index')->with('success','Data staf ditambahkan.');
    }
    public function edit(Staff $staff) { return view('staff.form', compact('staff')); }
    public function update(Request $request, Staff $staff)
    {
        $staff->update($this->validated($request, $staff));
        return redirect()->route('staff.index')->with('success','Data staf diperbarui.');
    }
    public function toggle(Staff $staff)
    {
        $staff->update(['is_active' => !$staff->is_active]);
        return back()->with('success','Status staf diperbarui. Riwayat jadwal tetap disimpan.');
    }
    private function validated(Request $request, ?Staff $staff = null): array
    {
        return $request->validate([
            'code'=>['required','string','max:25',Rule::unique('staff','code')->ignore($staff?->id)],
            'name'=>['required','string','max:150'],
            'phone'=>['nullable','string','max:30'], 'skill'=>['nullable','string','max:120'],
        ]);
    }
}
