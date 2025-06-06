<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Models\Seccion;
use Illuminate\Database\Seeder;
use Faker\Factory;

class EstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Factory::create('es_ES');
        
        $secciones = Seccion::all();

        for ($i = 1; $i <= 50; $i++) {
            $seccion = $secciones->random();
            
            Estudiante::create([
                'codigo_estudiante' => 'E' . str_pad($i, 4, '0', STR_PAD_LEFT),
                'nombres' => $faker->firstName,
                'apellidos' => $faker->lastName . ' ' . $faker->lastName,
                'fecha_nacimiento' => $faker->dateTimeBetween('-18 years', '-10 years'),
                'genero' => $faker->randomElement(['M', 'F']),
                'seccion_id' => $seccion->id,
                'fecha_ingreso' => $faker->dateTimeBetween('-2 years', 'now'),
                'estado' => $faker->randomElement(['activo', 'inactivo', 'egresado']),
                'direccion' => $faker->address,
                'observaciones' => $faker->paragraph,
                'contacto_emergencia_nombre' => $faker->name,
                'contacto_emergencia_parentesco' => $faker->randomElement(['padre', 'madre', 'tío', 'tía', 'hermano', 'hermana']),
                'contacto_emergencia_telefono' => $faker->phoneNumber
            ]);
        }
    }
}
