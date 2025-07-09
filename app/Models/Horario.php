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
        return $this->belongsTo(Asignacion::class, 'asignacion_id');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class, 'asignacion_id', 'id');
    }

    public function seccion()
    {
        return $this->belongsTo(Asignacion::class, 'asignacion_id')
                    ->with('seccion');
    }

    public function grado()
    {
        return $this->belongsTo(Asignacion::class, 'asignacion_id')
                    ->with('seccion.grado');
    }

    public function asistencia()
    {
        return $this->hasMany(Asistencia::class);
    }
}
