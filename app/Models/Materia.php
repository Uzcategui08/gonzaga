<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Materia extends Model
{
    protected $fillable = [
        'nombre',
        'nivel'
    ];

    public function grados()
    {
        return $this->belongsToMany(Grado::class, 'grado_materia');
    }

    public function secciones()
    {
        return $this->belongsToMany(Seccion::class, 'asignaciones', 'materia_id', 'seccion_id');
    }

    public function horarios()
    {
        return $this->hasManyThrough(
            Horario::class, 
            Asignacion::class,
            'materia_id', 
            'asignacion_id', 
            'id', 
            'id' 
        );
    }

    public function profesores()
    {
        return $this->belongsToMany(Profesor::class, 'asignaciones', 'materia_id', 'profesor_id');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(
            Estudiante::class,
            'asignaciones',
            'materia_id',
            'seccion_id'
        )->join('secciones', 'asignaciones.seccion_id', '=', 'secciones.id')
         ->join('estudiantes as e', 'secciones.id', '=', 'e.seccion_id')
         ->select('e.*');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function asistencias()
    {
        return $this->hasMany(Asistencia::class, 'materia_id', 'id');
    }
}
