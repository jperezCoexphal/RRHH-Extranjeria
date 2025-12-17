<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForeignerExtraData extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // CONFIG
    protected $primaryKey = 'foreigner_id';
    public $incrementing = false;
    public $timestamps = false;

        // FILLABLE
    protected $fillable = [
        'father_name',
        'mother_name',
        'legal_guardian_name',
        'legal_guardian_identity_number',
        'legal_guadian_title',
        'phone',
        'email',
    ];

}
