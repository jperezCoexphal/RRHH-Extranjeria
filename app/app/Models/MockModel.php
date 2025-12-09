<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MockModel extends Model
{
    use HasFactory;

    // Identifica la tabla en caso de violar la convecion de nombres
    protected $table = 'mock_models';

    // Determina que atributos son manipulables o seteables a través del modelo
    protected $fillable = [
        'name',
        'lastname',
        'age',
        'country',
        'birthdate',
        'password'
    ];

    // Campos protegidos, no manipulables o seteables a través del modelo.
    protected $guarded = [
        'uuid'
    ];
    
    // Elimina campos de la serialización de datos.
    protected $hidden = [
        'lastname',
        'age'
    ];

    // Nos sirve para castear datos a formatos o tipos concretos.
    protected $casts = [
        'birthdate' => 'date',
        'password' => 'hashed'
    ];
}