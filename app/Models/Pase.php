<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pase extends Model
{
    protected $fillable = [
        'estudiante_id',
        'motivo',
        'observaciones',
        'fecha',
        'hora_llegada',
        'aprobado',
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'hora_llegada' => 'datetime:H:i',
        'aprobado' => 'boolean'
    ];

    public function estudiante()
    {
        return $this->belongsTo(Estudiante::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeTarde($query)
    {
        return $query->where('hora_llegada', '>', config('app.hora_inicio_clases'));
    }
}
