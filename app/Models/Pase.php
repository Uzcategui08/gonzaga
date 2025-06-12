<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pase extends Model
{
    protected $fillable = [
        'estudiante_id',
        'horario_id',
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

    public function horario()
    {
        return $this->belongsTo(Horario::class);
    }

    public function profesor()
    {
        return $this->belongsTo(Profesor::class)
            ->through('horario', 'asignacion');
    }

    public function getProfesorNombreAttribute()
    {
        return $this->profesor?->user?->name;
    }

    public function getMateriaNombreAttribute()
    {
        return $this->horario?->asignacion?->materia?->nombre;
    }

    public function getSeccionNombreAttribute()
    {
        return $this->horario?->asignacion?->seccion?->nombre;
    }

    public function getHoraSalidaAttribute()
    {
        return $this->attributes['hora_salida'] ?? null;
    }

    public function getMotivoSalidaAttribute()
    {
        return $this->attributes['motivo_salida'] ?? null;
    }

    public function getNotificadoAttribute()
    {
        return $this->attributes['notificado'] ?? false;
    }

    public function scopeTarde($query)
    {
        return $query->where('hora_llegada', '>', config('app.hora_inicio_clases'));
    }
}
