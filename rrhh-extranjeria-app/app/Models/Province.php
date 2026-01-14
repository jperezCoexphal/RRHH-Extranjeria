<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Province extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'province_name',
        'province_code',
        'country_id',
    ];

        // TIMESTAMPS
    public $timestamps = false;

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }

    public function municipalities(): HasMany
    {
        return $this->hasMany(Municipality::class, 'province_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'province_id');
    }
}
