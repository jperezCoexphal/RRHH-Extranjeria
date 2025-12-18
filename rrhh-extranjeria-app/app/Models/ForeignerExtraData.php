<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ForeignerExtraData extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // CONFIG
    protected $table = 'foreigners_extra_data';
    protected $primaryKey = 'foreigner_id';
    public $incrementing = false;
    public $timestamps = false;

        // FILLABLE
    protected $fillable = [
        'foreigner_id',
        'father_name',
        'mother_name',
        'legal_guardian_name',
        'legal_guardian_identity_number',
        'guardianship_title',
        'phone',
        'email',
    ];

}
