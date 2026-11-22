<?php
namespace App\Http\Controllers;

use App\Models\WorkLocation;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LocationController extends Controller
{
    public function index() { return view('locations.index', ['locations'=>WorkLocation::orderBy('name')->get()]); }
    public function create() { return view('locations.form', ['location'=>new WorkLocation()]); }
    public function store(Request $request)
    {
        WorkLocation::create($this->validated($request));
        return redirect()->route('locations.index')->with('success','Lokasi ditambahkan.');
    }
    public function edit(WorkLocation $location) { return view('locations.form', compact('location')); }
    public function update(Request $request, WorkLocation $location)
    {
        $location->update($this->validated($request, $location));
        return redirect()->route('locations.index')->with('success','Lokasi diperbarui.');
    }
    private function validated(Request $request, ?WorkLocation $location = null): array
    {
        return $request->validate([
            'name'=>['required','string','max:150',Rule::unique('work_locations','name')->ignore($location?->id)],
            'address'=>['nullable','string','max:1000'], 'is_active'=>['required','boolean'],
        ]);
    }
}
