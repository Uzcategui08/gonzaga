<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Asignacion;
use App\Http\Controllers\AsignacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;

class ReguardarAsignaciones extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Example: php artisan asignaciones:reguardar
     */
    protected $signature = 'asignaciones:reguardar {--ids=* : IDs específicos de asignaciones a procesar}';

    /**
     * The console command description.
     */
    protected $description = 'Simula presionar "Editar y Guardar" para todas o algunas asignaciones sin cambiar nada.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ids = $this->option('ids');
        $query = Asignacion::query();

        if (!empty($ids)) {
            $query->whereIn('id', $ids);
        }

        $asignaciones = $query->get();

        if ($asignaciones->isEmpty()) {
            $this->warn('No se encontraron asignaciones para procesar.');
            return;
        }

        $controller = App::make(AsignacionController::class);
        $this->info("Procesando {$asignaciones->count()} asignaciones...");

        foreach ($asignaciones as $asignacion) {
            try {
                // Crear una request simulando el formulario original
                $request = Request::create(
                    route('asignaciones.update', $asignacion->id),
                    'PUT',
                    [
                        'profesor_id' => $asignacion->profesor_id,
                        'materia_id' => $asignacion->materia_id,
                        'seccion_id' => $asignacion->seccion_id,
                        'estudiantes_id' => $asignacion->estudiantes()->pluck('estudiantes.id')->toArray(),
                    ]
                );

                $controller->update($request, $asignacion);

                $this->info("Asignación ID {$asignacion->id} reguardada correctamente.");
            } catch (\Throwable $e) {
                Log::error("Error reguardando asignación {$asignacion->id}: " . $e->getMessage());
                $this->error("Error en asignación {$asignacion->id}: {$e->getMessage()}");
            }
        }

        $this->info('✅ Proceso completado.');
    }
}
