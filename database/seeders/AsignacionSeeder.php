<?php

namespace Database\Seeders;

use App\Models\Asignacion;
use App\Models\Profesor;
use App\Models\Materia;
use App\Models\Seccion;
use Illuminate\Database\Seeder;

class AsignacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profesores = Profesor::all();
        $materias = Materia::all();

        $secciones = Seccion::all();
        foreach ($secciones as $seccion) {
            $materiasGrado = $seccion->grado->materias;
            
            foreach ($materiasGrado as $materia) {
                $profesor = $profesores->random();
                
                Asignacion::create([
                    'seccion_id' => $seccion->id,
                    'materia_id' => $materia->id,
                    'profesor_id' => $profesor->id
                ]);
            }
        }
    }
}
