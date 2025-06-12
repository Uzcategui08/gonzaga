<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Limpieza extends Model
{
    protected $fillable = [
        'profesor_id',
        'horario_id',
        'fecha',
        'hora_inicio',
        'hora_fin',
        'realizada',
        'estudiantes_tareas',
        'observaciones',
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
        'realizada' => 'boolean',
        'estudiantes_tareas' => 'array'
    ];

    public function profesor(): BelongsTo
    {
        return $this->belongsTo(Profesor::class);
    }

    public function getEstudiantesConTareasAttribute()
    {
        return collect($this->estudiantes_tareas ?? []);
    }

    public function setEstudiantesConTareasAttribute($value)
    {
        $this->attributes['estudiantes_tareas'] = json_encode($value);
    }

    public function horario(): BelongsTo
    {
        return $this->belongsTo(Horario::class);
    }

    public function estudiantes(): BelongsToMany
    {
        return $this->belongsToMany(Estudiante::class, 'limpieza_estudiante', 'limpieza_id', 'estudiante_id');
    }

    public function getAulaNameAttribute()
    {
        if ($this->horario) {
            return $this->horario->aula;
        }
        return 'Aula por asignar';
    }
}

      
