<?php

namespace Database\Seeders;

use App\Models\Grado;
use App\Models\Materia;
use Illuminate\Database\Seeder;

class GradoMateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grados = Grado::all();
        $materias = Materia::all();
        
        foreach ($grados as $grado) {
            $materiasNivel = $materias->where('nivel', $grado->nivel);
            
            $grado->materias()->attach($materiasNivel->pluck('id'));
        }
    }
}
