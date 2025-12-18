<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Freelancer extends Model
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
        'first_name',
        'last_name',
        'niss',
        'birthdate'
    ];

        // CASTS
    protected $casts = [
        'birthdate' => 'date'
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function employer(): BelongsTo
    {
        return $this->belongsTo(Employer::class);
    }

}
