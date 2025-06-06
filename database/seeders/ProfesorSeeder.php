<?php

namespace Database\Seeders;

use App\Models\Profesor;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProfesorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usuarios = User::where('tipo', 'profesor')->get();
        
        foreach ($usuarios as $usuario) {
            Profesor::create([
                'user_id' => $usuario->id,
                'codigo_profesor' => 'P' . str_pad($usuario->id, 3, '0', STR_PAD_LEFT),
                'especialidad' => $this->getEspecialidad($usuario->name),
                'fecha_contratacion' => now()->subYears(rand(1, 5)),
                'tipo_contrato' => $this->getTipoContrato()
            ]);
        }
    }

    private function getEspecialidad($nombre)
    {
        $especialidades = [
            'Matemáticas', 'Lengua y Literatura', 'Ciencias Naturales',
            'Educación Física', 'Educación Artística', 'Educación en Valores',
            'Biología', 'Física', 'Química', 'Historia', 'Geografía', 'Inglés'
        ];
        return $especialidades[array_rand($especialidades)];
    }

    private function getTipoContrato()
    {
        $tipos = ['Tiempo completo', 'Medio tiempo', 'Contrato por horas'];
        return $tipos[array_rand($tipos)];
    }
}
