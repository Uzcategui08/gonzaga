<?php

namespace App\Http\Controllers;

use App\Models\Seccion;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Asignacion;
use App\Models\Horario;
use Illuminate\Http\Request;

class HorarioPrimariaController extends Controller
{
    public function edit(Seccion $seccion)
    {
        $seccion->load(['grado', 'titular.user']);
        $materias = Materia::orderBy('nombre')->get();
        $profesores = Profesor::with('user')->join('users', 'profesores.user_id', '=', 'users.id')->orderBy('users.name')->get(['profesores.*']);

        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $horarios = Horario::with('asignacion.materia', 'asignacion.profesor.user')
            ->whereHas('asignacion', fn($q) => $q->where('seccion_id', $seccion->id))
            ->orderBy('dia')->orderBy('hora_inicio')->get();

        // Agrupar por día para precargar en la vista
        $porDia = [];
        foreach ($dias as $d) $porDia[$d] = [];
        foreach ($horarios as $h) {
            $porDia[$h->dia][] = $h;
        }

        return view('horarios.primaria.edit', compact('seccion', 'materias', 'profesores', 'dias', 'porDia'));
    }

    public function update(Request $request, Seccion $seccion)
    {
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $data = $request->validate([
            'schedule' => 'required|array',
        ]);

        $schedule = $data['schedule'];

        foreach ($schedule as $dia => $entradas) {
            if (!in_array($dia, $dias)) continue;
            if (!is_array($entradas)) continue;

            foreach ($entradas as $entrada) {
                $entrada = array_merge([
                    'hora_inicio' => null,
                    'hora_fin' => null,
                    'materia_id' => null,
                    'aula' => null,
                    'profesor_id' => null,
                ], (array)$entrada);

                if (!$entrada['hora_inicio'] || !$entrada['hora_fin'] || !$entrada['materia_id']) {
                    continue;
                }

                $profesorId = $entrada['profesor_id'] ?: $seccion->titular_profesor_id;
                if (!$profesorId) {
                    // Sin titular ni override, omitir
                    continue;
                }

                // Asignación profesor-materia-sección
                $asignacion = Asignacion::firstOrCreate([
                    'profesor_id' => $profesorId,
                    'materia_id' => $entrada['materia_id'],
                    'seccion_id' => $seccion->id,
                ], [
                    'estudiantes_id' => json_encode([]),
                ]);

                // Upsert del horario
                $horario = Horario::where('asignacion_id', $asignacion->id)
                    ->where('dia', $dia)
                    ->where('hora_inicio', $entrada['hora_inicio'])
                    ->where('hora_fin', $entrada['hora_fin'])
                    ->first();

                if ($horario) {
                    $horario->update([
                        'aula' => $entrada['aula'],
                    ]);
                } else {
                    Horario::create([
                        'asignacion_id' => $asignacion->id,
                        'dia' => $dia,
                        'hora_inicio' => $entrada['hora_inicio'],
                        'hora_fin' => $entrada['hora_fin'],
                        'aula' => $entrada['aula'],
                    ]);
                }
            }
        }

        return redirect()->route('horarios.primaria.edit', $seccion)
            ->with('success', 'Horario de primaria actualizado');
    }
}
