<?php

namespace App\Models;

use App\Enums\LegalForm;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employer extends Model
{
    use HasFactory, SoftDeletes;

    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'legal_form',
        'comercial_name',
        'fiscal_name',
        'nif',
        'ccc',
        'cnae',
        'email',
        'phone',
        'is_associated'
    ];

        // CASTS
    protected $casts = [
        'legal_form' => LegalForm::class,
        'is_associated' => 'boolean',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function freelancer(): HasOne
    {
        return $this->hasOne(Freelancer::class, 'employer_id');
    }

    public function company(): HasOne
    {
        return $this->hasOne(Company::class, 'employer_id');
    }

    /**
     * Dirección del empleador (relación polimórfica)
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * Expedientes de inmigración asociados al empleador
     */
    public function inmigrationFiles(): HasMany
    {
        return $this->hasMany(InmigrationFile::class, 'employer_id');
    }

    // ACCESSORS
    /*-----------------------------------------------------------------------------------*/

    // public function detail(): Attribute
    // {
    //     return Attribute::get(function () {
    //         return match ($this->legal_type) {
    //             LegalType::Freelancer => $this->freelancer,
    //             LegalType::Company => $this->company,
    //             default => null,
    //         };
    //     });
    // }
    

}       
