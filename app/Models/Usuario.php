<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Model
{
   
    use Notifiable;

    protected $table = 'Usuarios'; // Nombre de la tabla en la base de datos
    protected $primaryKey = 'DocumentoId'; // Llave primaria
    public $incrementing = false; // Indica que no es autoincremental
    protected $keyType = 'string'; // Tipo de dato de la llave primaria
    protected $fillable = [
        'DocumentoId', 'Nombre', 'password'
    ];

    protected $hidden = [
        'password',
    ];
}
