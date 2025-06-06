<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\Grado;
use Illuminate\Database\Seeder;

class MateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        $primaria = Grado::where('nivel', 'Primaria')->get();
        foreach ($primaria as $grado) {
            $grado->materias()->attach([
                Materia::create(['nombre' => 'Matemáticas', 'nivel' => 'primaria'])->id,
                Materia::create(['nombre' => 'Lengua y Literatura', 'nivel' => 'primaria'])->id,
                Materia::create(['nombre' => 'Ciencias Naturales', 'nivel' => 'primaria'])->id,
                Materia::create(['nombre' => 'Educación Física', 'nivel' => 'primaria'])->id,
                Materia::create(['nombre' => 'Educación Artística', 'nivel' => 'primaria'])->id,
                Materia::create(['nombre' => 'Educación en Valores', 'nivel' => 'primaria'])->id,
            ]);
        }


        $secundaria = Grado::where('nivel', 'Secundaria')->get();
        foreach ($secundaria as $grado) {
            $grado->materias()->attach([
                Materia::create(['nombre' => 'Matemáticas Avanzadas', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Lengua y Literatura Avanzada', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Biología', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Física', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Química', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Historia', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Geografía', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Inglés', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Educación Física Avanzada', 'nivel' => 'secundaria'])->id,
                Materia::create(['nombre' => 'Educación Cívica', 'nivel' => 'secundaria'])->id,
            ]);
        }
    }
}
