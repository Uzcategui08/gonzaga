<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Notifications\Notifiable;

class Profesor extends User
{
    use Notifiable;

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

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'asignaciones', 'profesor_id', 'materia_id');
    }

    public function secciones()
    {
        return $this->belongsToMany(Seccion::class, 'asignaciones', 'profesor_id', 'seccion_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function limpiezas()
    {
        return $this->hasMany(Limpieza::class);
    }
}
