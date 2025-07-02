<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Asignacion extends Model
{
    protected $table = 'asignaciones';
    
    protected $fillable = [
        'profesor_id',
        'materia_id',
        'seccion_id'
    ];


    public function profesor()
    {
        return $this->belongsTo(Profesor::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'profesor_id', 'id')
            ->select('id', 'name', 'email');
    }

    public function materia()
    {
        return $this->belongsTo(Materia::class);
    }

    public function seccion()
    {
        return $this->belongsTo(Seccion::class);
    }

    public function grado()
    {
        return $this->belongsTo(Grado::class, 'seccion_id', 'id')->through(Seccion::class);
    }

    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    public function estudiantes()
    {
        return $this->hasManyThrough(
            Estudiante::class,
            Seccion::class,
            'id', 
            'seccion_id', 
            'seccion_id', 
            'id' 
        );
    }
}
