<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grado extends Model
{
    protected $fillable = ['nombre', 'nivel'];

    protected $table = 'grados';

    public function secciones()
    {
        return $this->hasMany(Seccion::class);
    }

    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'grado_materia');
    }
}
