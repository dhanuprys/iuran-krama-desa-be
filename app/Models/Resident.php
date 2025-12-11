<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resident extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'nik',
        'user_id',
        'created_by_user_id',
        'banjar_id',
        'family_card_number',
        'name',
        'gender',
        'place_of_birth',
        'date_of_birth',
        'family_status',
        'religion',
        'education',
        'work_type',
        'marital_status',
        'origin_address',
        'residential_address',
        'rt_number',
        'residence_name',
        'house_number',
        'location',
        'arrival_date',
        'phone',
        'email',
        'validation_status',
        'photo_house',
        'resident_photo',
        'photo_ktp',
        'resident_status_id',
        'rejection_reason',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'arrival_date' => 'date',
        'location' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function residentStatus(): BelongsTo
    {
        return $this->belongsTo(ResidentStatus::class);
    }

    public function banjar(): BelongsTo
    {
        return $this->belongsTo(Banjar::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
