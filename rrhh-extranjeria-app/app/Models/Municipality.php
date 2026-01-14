<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Municipality extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'municipality_name',
        'municipality_code',
        'province_id',
    ];

        // TIMESTAMPS
    public $timestamps = false;

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function province(): BelongsTo
    {
        return $this->belongsTo(Province::class, 'province_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'municipality_id');
    }
}
