<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AsistenciaEstudiante extends Model
{
    protected $table = 'asistencia_estudiante';
    
    protected $fillable = [
        'asistencia_id',
        'estudiante_id',
        'estado',
        'observacion_individual'
    ];

    protected $casts = [
        'estado' => 'string'
    ];

    public function asistencia()
    {
        return $this->belongsTo(Asistencia::class);
    }

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }
}
