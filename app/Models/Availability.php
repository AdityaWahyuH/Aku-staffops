<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Availability extends Model
{
    protected $table = 'availabilities';
    protected $fillable = ['staff_id', 'date', 'status', 'notes', 'updated_by'];
    protected function casts(): array { return ['date' => 'date']; }
    public function staff() { return $this->belongsTo(Staff::class); }
}
