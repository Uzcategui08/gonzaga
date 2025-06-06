<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    protected $fillable = [
        'profesor_id',
        'materia_id',
        'grado_id',
        'fecha',
        'hora_inicio',
        'observacion_general',
        'falta_justificada',
        'tarea_pendiente',
        'conducta',
        'contenido_clase',
        'aula',
        'horario_id',
        'pase_salida',
        'retraso',
        's_o'
    ];

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    protected $casts = [
        'fecha' => 'date',
        'falta_justificada' => 'boolean',
        'tarea_pendiente' => 'boolean',
        'conducta' => 'boolean',
        'pase_salida' => 'boolean',
        'retraso' => 'boolean',
        's_o' => 'boolean'
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    public function materia(): BelongsTo
    {
        return $this->belongsTo(Materia::class);
    }

    public function grado(): BelongsTo
    {
        return $this->belongsTo(Grado::class);
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'asistencia_estudiante')
            ->withPivot(['estado', 'observacion_individual']);
    }
}
