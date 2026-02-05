<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CnoCode extends Model
{
    // ATTRIBUTES
    /*-----------------------------------------------------------------------------------*/

        // FILLABLE
    protected $fillable = [
        'code',
        'description',
        'group_code',
    ];

    // RELATIONSHIPS
    /*-----------------------------------------------------------------------------------*/

    public function inmigrationFiles(): HasMany
    {
        return $this->hasMany(InmigrationFile::class, 'cno_code_id');
    }

    // SCOPES
    /*-----------------------------------------------------------------------------------*/

    /**
     * Busca por código o descripción
     */
    public function scopeSearch($query, string $term)
    {
        return $query->where('code', 'like', "{$term}%")
            ->orWhere('description', 'like', "%{$term}%");
    }

    /**
     * Filtra por grupo primario (4 dígitos)
     */
    public function scopeByGroup($query, string $groupCode)
    {
        return $query->where('group_code', $groupCode);
    }
}
