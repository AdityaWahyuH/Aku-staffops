<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleBatch extends Model
{
    protected $fillable = ['schedule_date', 'location_id', 'generated_by', 'required_count', 'created_count', 'shortage_count', 'notes'];
    protected function casts(): array { return ['schedule_date' => 'date']; }
    public function assignments() { return $this->hasMany(ScheduleAssignment::class, 'batch_id'); }
    public function location() { return $this->belongsTo(WorkLocation::class, 'location_id'); }
}
