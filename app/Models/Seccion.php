<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Seccion extends Model
{
    protected $table = 'secciones';
    
    protected $fillable = [
        'nombre',
        'grado_id'
    ];

    public function grado()
    {
        return $this->belongsTo(Grado::class);
    }

    public function estudiantes()
    {
        return $this->hasMany(Estudiante::class);
    }

    public function asignaciones()
    {
        return $this->hasMany(Asignacion::class);
    }

    public function coordinators()
    {
        return $this->belongsToMany(User::class, 'coordinator_section', 'section_id', 'coordinator_id')
                    ->using(CoordinatorSection::class)
                    ->withTimestamps();
    }
}
