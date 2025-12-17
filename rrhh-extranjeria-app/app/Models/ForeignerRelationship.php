<?php

namespace App\Models;

use App\Enums\RelationType;
use Illuminate\Database\Eloquent\Relations\Pivot;

class ForeignerRelationship extends Pivot
{
    
    protected $table = 'foreigner_relationships';

    protected $casts = [
        'relation_type' => RelationType::class,
    ];
}
