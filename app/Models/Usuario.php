<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable
{
    use Notifiable;

    protected $table = 'Usuarios'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'DocumentoId'; // Clave primaria de la tabla
    public $incrementing = false; // Indica que la clave primaria no es autoincremental
    protected $keyType = 'string'; // Tipo de la clave primaria

    protected $fillable = [
        'DocumentoId', 'IdGrupo', 'Nombre', 'Carrera', 'Semestre', 'Direccion', 'Telefono', 'password',
    ];

    protected $hidden = [
        'password',
    ];
}