<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;

class Usuario extends Authenticatable
{
    use HasFactory, HasApiTokens;

    protected $table = 'Usuarios';
    protected $primaryKey = 'DocumentoId';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'DocumentoId',
        'Nombre',
        'Apellido',
        'Direccion',
        'Telefono',
        'Email',
        'password'
    ];
    
    protected $hidden = [
        'password',
        'remember_token'
    ];
}