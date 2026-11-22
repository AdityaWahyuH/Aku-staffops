<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Staff extends Model
{
    protected $table = 'staff';
    protected $fillable = ['code', 'name', 'phone', 'skill', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function availability() { return $this->hasMany(Availability::class); }
    public function assignments() { return $this->hasMany(ScheduleAssignment::class); }
}
