<?php

namespace Database\Seeders;

use App\Models\Horario;
use App\Models\Asignacion;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        $horasInicio = ['07:30', '08:30', '09:30', '10:30', '11:30', '12:30', '13:30', '14:30'];
        $aulas = ['A101', 'A102', 'A103', 'A201', 'A202', 'A203', 'A301', 'A302', 'A303'];

        $asignaciones = Asignacion::all()->groupBy('user_id'); // Agrupa por profesor

        foreach ($asignaciones as $userId => $asignacionesProfesor) {
            foreach ($dias as $dia) {
                // Selecciona 3 asignaciones aleatorias para el profesor
                $asignacionesDia = $asignacionesProfesor->shuffle()->take(3);

                // Selecciona 3 horas de inicio aleatorias y únicas para el día
                $horasDia = collect($horasInicio)->shuffle()->take(3);

                foreach ($asignacionesDia as $idx => $asignacion) {
                    $horaInicio = $horasDia[$idx];
                    $horaFin = Carbon::parse($horaInicio)->addHours(1)->format('H:i');
                    $aula = $aulas[array_rand($aulas)];

                    Horario::create([
                        'asignacion_id' => $asignacion->id,
                        'dia' => $dia,
                        'hora_inicio' => $horaInicio,
                        'hora_fin' => $horaFin,
                        'aula' => $aula
                    ]);
                }
            }
        }
    }
}
