<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MaritalStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'gender',
        'birthdate',
        'nationality',
        'marital_status',
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
