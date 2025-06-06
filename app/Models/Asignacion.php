<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
}
