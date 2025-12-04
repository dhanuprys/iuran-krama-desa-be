<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResidentStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'contribution_amount',
    ];

    protected $casts = [
        'contribution_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function residents(): HasMany
    {
        return $this->hasMany(Resident::class);
    }
}
