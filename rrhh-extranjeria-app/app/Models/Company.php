<?php

namespace App\Models;

use App\Enums\LegalForm;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Company extends Model
{
    use HasFactory;

    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // CONFIG
    protected $primaryKey = 'employer_id';
    public $incrementing = false;
    public $timestamps = false;

        // FILLABLE
    protected $fillable = [
        'employer_id',
        'representative_name',
        'representative_title',
        'representantive_identity_number',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }
}
