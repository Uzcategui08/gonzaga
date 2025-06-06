<?php

namespace Database\Seeders;

use App\Models\Grado;
use Illuminate\Database\Seeder;

class GradoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Grado::create([
            'nombre' => 'Primero',
            'nivel' => 'Primaria'
        ]);
        
        Grado::create([
            'nombre' => 'Segundo',
            'nivel' => 'Primaria'
        ]);
        
        Grado::create([
            'nombre' => 'Tercero',
            'nivel' => 'Primaria'
        ]);

        Grado::create([
            'nombre' => 'Sexto',
            'nivel' => 'Secundaria'
        ]);
        
        Grado::create([
            'nombre' => 'Séptimo',
            'nivel' => 'Secundaria'
        ]);
        
        Grado::create([
            'nombre' => 'Octavo',
            'nivel' => 'Secundaria'
        ]);
    }
}
