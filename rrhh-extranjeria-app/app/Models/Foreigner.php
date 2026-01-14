<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Foreigner extends Model
{
    use SoftDeletes;

    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'first_name',
        'last_name',
        'passport',
        'nie',
        'niss',
        'gender',
        'birthdate',
        'marital_status',
        'nationality_id',
        'birth_country_id',
        'birthplace_name',
    ];

        // CASTS
    protected $casts = [
        'gender' => Gender::class,
        'marital_status' => MaritalStatus::class,
        'birthdate' => 'date',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function extraData(): HasOne
    {
        return $this->hasOne(ForeignerExtraData::class, 'foreigner_id');
    }

    /**
     * Nacionalidad del extranjero
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    /**
     * País de nacimiento
     */
    public function birthCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'birth_country_id');
    }

    /**
     * Dirección del extranjero (relación polimórfica)
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * Expedientes de inmigración del extranjero
     */
    public function inmigrationFiles(): HasMany
    {
        return $this->hasMany(InmigrationFile::class, 'foreigner_id');
    }

    public function relationships(): BelongsToMany
    {
        return $this->belongsToMany(
            Foreigner::class, 
            'foreigner_relationships', 
            'foreigner_id', 
            'related_foreigner_id'
        )
        ->using(ForeignerRelationship::class)
        ->withPivot('relation_type')
        ->withTimestamps();
    }

    public function relatedBy(): BelongsToMany
    {
        return $this->belongsToMany(
            Foreigner::class,
            'foreigner_relationships',
            'related_foreigner_id',
            'foreigner_id'
        )
        ->using(ForeignerRelationship::class)
        ->withPivot('realtion_type')
        ->withTimestamps();
    }

    // ACCESSORS
    /*-----------------------------------------------------------------------------------*/

    protected function allRelationships(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->relationships->merge($this->relatedBy),
        );
    }
}
