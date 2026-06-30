<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'last_name', 'first_name', 'middle_name', 'extension_name',
        'sex', 'date_of_birth', 'tin', 'pwd', 'ip_group_member', 'ethnicity',
        'position_title', 'position_from', 'position_to',
        'salary_grade', 'salary_grade_step', 'monthly_salary',
        'employee_status', 'compensation_words', 'compensation_numbers',
        'nature_of_appointment', 'reason', 'position_level', 'appointment_status',
        'department', 'school_district', 'sector', 'agency_name',
        'plantilla_item_number', 'plantilla_page_number', 'odc_number',
        'date_received_records', 'date_received_hr', 'previous_incumbent',
        'incumbent', 'publication_mode',
        'eligibility_type', 'eligibility_validity', 'eligibility_first_used',
        'date_original_appointment', 'date_last_promotion',
        'record_state', 'conclusion_reason', 'date_concluded',
        'encoding_personnel', 'encoded_at',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'position_from' => 'date',
        'position_to' => 'date',
        'date_received_records' => 'date',
        'date_received_hr' => 'date',
        'eligibility_validity' => 'date',
        'date_original_appointment' => 'date',
        'date_last_promotion' => 'date',
        'date_concluded' => 'date',
        'encoded_at' => 'datetime',
        'monthly_salary' => 'decimal:2',
        'compensation_numbers' => 'decimal:2',
    ];

    /* ── Boot: auto-generate transaction number ── */
    protected static function booted(): void
    {
        static::creating(function (Appointment $appointment) {
            if (empty($appointment->transaction_number)) {
                $appointment->transaction_number = 'TN-' . now()->format('Y-M') . '-' . Str::lower(Str::random(13));
            }
            if (empty($appointment->encoded_at)) {
                $appointment->encoded_at = now();
            }
        });
    }

    /* ── Accessors ── */
    public function getFullNameAttribute(): string
    {
        $middleInitial = $this->middle_name ? ' ' . Str::substr($this->middle_name, 0, 1) . '.' : '';
        return "{$this->last_name}, {$this->first_name}{$middleInitial}";
    }

    /* ── Scopes ── */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('record_state', 'active');
    }

    public function scopeConcluded(Builder $query): Builder
    {
        return $query->where('record_state', 'concluded');
    }

    public function scopeEncodedOn(Builder $query, string $date): Builder
    {
        return $query->whereDate('encoded_at', $date);
    }

    public function scopeConcludedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        if ($from) {
            $query->whereDate('date_concluded', '>=', $from);
        }
        if ($to) {
            $query->whereDate('date_concluded', '<=', $to);
        }
        return $query;
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (! $term) {
            return $query;
        }
        return $query->where(function (Builder $q) use ($term) {
            $q->where('last_name', 'like', "%{$term}%")
              ->orWhere('first_name', 'like', "%{$term}%")
              ->orWhere('school_district', 'like', "%{$term}%")
              ->orWhere('eligibility_type', 'like', "%{$term}%")
              ->orWhere('nature_of_appointment', 'like', "%{$term}%")
              ->orWhere('reason', 'like', "%{$term}%");
        });
    }
}