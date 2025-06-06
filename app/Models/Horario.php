<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    protected $table = 'horarios';
    
    protected $fillable = [
        'asignacion_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
        'aula'
    ];

    public function asignacion()
    {
        return $this->belongsTo(Asignacion::class);
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'asignacion_id', 'id')->through(Asignacion::class);
    }
}
