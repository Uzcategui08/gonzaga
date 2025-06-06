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
        
        $asignaciones = Asignacion::all();
        
        foreach ($asignaciones as $asignacion) {
            for ($i = 0; $i < 3; $i++) {
                $dia = $dias[array_rand($dias)];
                $horaInicio = $horasInicio[array_rand($horasInicio)];
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
