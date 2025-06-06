<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Profesor extends Model
{
    protected $table = 'profesores';

    protected $fillable = [
        'user_id',
        'codigo_profesor',
        'especialidad',
        'fecha_contratacion',
        'tipo_contrato'
    ];

    protected $casts = [
        'fecha_contratacion' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'asignaciones', 'profesor_id', 'materia_id');
    }

    public function secciones()
    {
        return $this->belongsToMany(Seccion::class, 'asignaciones', 'profesor_id', 'seccion_id');
    }
}
