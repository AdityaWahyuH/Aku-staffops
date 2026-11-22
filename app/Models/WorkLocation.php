<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkLocation extends Model
{
    protected $fillable = ['name', 'address', 'is_active'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function requirements() { return $this->hasMany(ShiftRequirement::class, 'location_id'); }
}
