<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduleAssignment extends Model
{
    protected $fillable = ['batch_id', 'schedule_date', 'location_id', 'shift_id', 'staff_id', 'status', 'published_at', 'published_by'];
    protected function casts(): array { return ['schedule_date' => 'date', 'published_at' => 'datetime']; }
    public function batch() { return $this->belongsTo(ScheduleBatch::class, 'batch_id'); }
    public function staff() { return $this->belongsTo(Staff::class); }
    public function location() { return $this->belongsTo(WorkLocation::class, 'location_id'); }
    public function shift() { return $this->belongsTo(Shift::class); }
}
