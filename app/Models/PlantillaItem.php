<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class PlantillaItem extends Model
{
    protected $table = 'data';

    protected $fillable = [
        'level',
        'school_id',
        'school_name',
        'city_municipality',
        'data',
        'position',
        'sex',
        'eligibility',
        'first_time_used_of_eligibility',
        'position_level',
        'nature_of_appointment',
        'status_of_appointment',
    ];

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('data', 'like', "%{$term}%")
              ->orWhere('position', 'like', "%{$term}%")
              ->orWhere('school_name', 'like', "%{$term}%")
              ->orWhere('school_id', 'like', "%{$term}%");
        });
    }
}
