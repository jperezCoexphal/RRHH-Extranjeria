<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'country_name',
        'iso_code_2',
    ];

        // TIMESTAMPS
    public $timestamps = false;

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function provinces(): HasMany
    {
        return $this->hasMany(Province::class, 'country_id');
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'country_id');
    }

    public function foreignersByNationality(): HasMany
    {
        return $this->hasMany(Foreigner::class, 'nationality_id');
    }

    public function foreignersByBirthCountry(): HasMany
    {
        return $this->hasMany(Foreigner::class, 'birth_country_id');
    }
}
