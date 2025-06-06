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
            'materia_id', // local key en asignaciones
            'asignacion_id', // foreign key en horarios
            'id', // local key en materias
            'id' // foreign key en asignaciones
        );
    }

    public function profesores()
    {
        return $this->belongsToMany(Profesor::class, 'asignaciones', 'materia_id', 'profesor_id');
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }
}
