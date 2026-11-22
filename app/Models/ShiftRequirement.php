<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShiftRequirement extends Model
{
    protected $fillable = ['location_id', 'shift_id', 'required_count'];
    public function location() { return $this->belongsTo(WorkLocation::class, 'location_id'); }
    public function shift() { return $this->belongsTo(Shift::class); }
}
