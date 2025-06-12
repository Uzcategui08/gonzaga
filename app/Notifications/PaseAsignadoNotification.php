<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pase;
use App\Models\Estudiante;
use App\Models\Materia;
use Illuminate\Support\Facades\Log;

class PaseAsignadoNotification extends Notification
{
    use Queueable;

    protected $pase;
    protected $estudianteNombre;
    protected $motivo;
    protected $horaLlegada;
    protected $materiaNombre;
    protected $seccionNombre;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        Pase $pase,
        string $estudianteNombre,
        string $motivo,
        string $horaLlegada,
        ?string $materiaNombre = null,
        ?string $seccionNombre = null
    ) {
        $this->pase = $pase;
        $this->estudianteNombre = $estudianteNombre;
        $this->motivo = $motivo;
        $this->horaLlegada = $horaLlegada;
        $this->materiaNombre = $materiaNombre;
        $this->seccionNombre = $seccionNombre;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        Log::info('Notificación enviada al canal: database');
        Log::info('Notifiable ID: ' . $notifiable->id);
        Log::info('Notifiable Type: ' . get_class($notifiable));
        return ['database'];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        // Obtener los datos correctos del horario
        $materia = $this->pase->horario?->asignacion?->materia?->nombre ?? 'Desconocida';
        $seccion = $this->pase->horario?->asignacion?->seccion?->nombre ?? 'Desconocida';

        return [
            'id' => $this->pase->id,
            'estudiante' => $this->estudianteNombre,
            'motivo' => $this->motivo,
            'hora_llegada' => $this->horaLlegada,
            'materia' => $materia,
            'seccion' => $seccion,
            'fecha' => $this->pase->fecha,
            'aprobado' => $this->pase->aprobado,
            'observaciones' => $this->pase->observaciones,
            'created_at' => $this->pase->created_at
        ];
    }
}
