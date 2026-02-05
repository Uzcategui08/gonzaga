<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class ClaseExtracurricular extends Model
{
    protected $table = 'clases_extracurriculares';

    protected $fillable = [
        'nombre',
        'profesor_id',
        'aula',
        'descripcion',
        'hora_inicio',
        'hora_fin',
        'dia_semana',
        'activo',
        'created_by',
    ];

    protected $casts = [
        'activo' => 'boolean',
        'dia_semana' => 'integer',
    ];

    public function profesor()
    {
        return $this->belongsTo(Profesor::class, 'profesor_id');
    }

    public function scopeSoloActivas($query)
    {
        $table = $this->getTable();

        if (Schema::hasColumn($table, 'activo')) {
            return $query->where('activo', true);
        }

        if (Schema::hasColumn($table, 'activa')) {
            return $query->where('activa', true);
        }

        return $query;
    }

    public function estudiantes()
    {
        return $this->belongsToMany(Estudiante::class, 'clase_extracurricular_estudiante')
            ->withTimestamps();
    }

    public function asistencias()
    {
        return $this->hasMany(AsistenciaExtracurricular::class, 'clase_extracurricular_id');
    }
}
