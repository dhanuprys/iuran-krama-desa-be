<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory, \App\Traits\Auditable;

    protected $fillable = [
        'resident_id',
        'invoice_date',
        'iuran_amount',
        'peturunan_amount',
        'dedosan_amount',
        'total_amount',
        'user_id',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'iuran_amount' => 'decimal:2',
        'peturunan_amount' => 'decimal:2',
        'dedosan_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function resident(): BelongsTo
    {
        return $this->belongsTo(Resident::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
