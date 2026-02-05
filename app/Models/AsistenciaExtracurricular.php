<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaExtracurricular extends Model
{
    protected $table = 'asistencias_extracurriculares';

    protected $fillable = [
        'clase_extracurricular_id',
        'profesor_id',
        'fecha',
        'hora_inicio',
        'contenido_clase',
        'observacion_general',
        'created_by',
    ];

    protected $casts = [
        'fecha' => 'date',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }

    public function clase()
    {
        return $this->belongsTo(ClaseExtracurricular::class, 'clase_extracurricular_id');
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'asistencia_extracurricular_estudiante')
            ->withPivot(['estado', 'observacion_individual'])
            ->withTimestamps();
    }
}
