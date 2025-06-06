<?php

namespace Database\Seeders;

use App\Models\Seccion;
use App\Models\Grado;
use Illuminate\Database\Seeder;

class SeccionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grados = Grado::all();
        
        foreach ($grados as $grado) {
            Seccion::create([
                'nombre' => $grado->nombre . 'A',
                'grado_id' => $grado->id
            ]);

            Seccion::create([
                'nombre' => $grado->nombre . 'B',
                'grado_id' => $grado->id
            ]);
        }
    }
}
