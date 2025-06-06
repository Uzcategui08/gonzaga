<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Estudiante extends Model
{
    protected $table = 'estudiantes';
    
    protected $fillable = [
        'seccion_id',
        'codigo_estudiante',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'genero',
        'direccion',
        'estado',
        'fecha_ingreso',
        'observaciones',
        'contacto_emergencia_nombre',
        'contacto_emergencia_parentesco',
        'contacto_emergencia_telefono'
    ];

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }
}
