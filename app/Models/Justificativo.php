<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Justificativo extends Model
{
    protected $casts = [
        'fecha_inicio' => 'datetime:Y-m-d',
        'fecha_fin' => 'datetime:Y-m-d'
    ];

    protected $fillable = [
        'estudiante_id',
        'user_id',
        'fecha_inicio',
        'fecha_fin',
        'motivo',
        'observaciones',
        'tipo',
        'aprobado'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
