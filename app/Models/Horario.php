<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Grupo;

class Horario extends Model
{
    protected $table = 'horarios';
    
    protected $fillable = [
        'asignacion_id',
        'dia',
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

    public function seccion()
    {
        return $this->belongsTo(Seccion::class, 'asignacion_id', 'id')->through(Asignacion::class);
    }
}
