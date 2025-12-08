<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Family extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'families';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'family_card_number';

    /**
     * The "type" of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false; // View doesn't have timestamps

    public function headOfFamily(): HasOne
    {
        return $this->hasOne(Resident::class, 'family_card_number', 'family_card_number')
            ->where('family_status', 'HEAD_OF_FAMILY');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Resident::class, 'family_card_number', 'family_card_number');
    }
}
