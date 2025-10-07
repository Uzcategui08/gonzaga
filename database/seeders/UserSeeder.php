<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $profesores = [
            [
                'name' => 'Juan Pérez',
                'email' => 'juan.perez@colegio.com',
                'password' => bcrypt('profesor123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'tipo' => 'profesor'
            ],
            [
                'name' => 'María García',
                'email' => 'maria.garcia@colegio.com',
                'password' => bcrypt('profesor123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'tipo' => 'profesor'
            ],
            [
                'name' => 'Carlos López',
                'email' => 'carlos.lopez@colegio.com',
                'password' => bcrypt('profesor123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'tipo' => 'profesor'
            ],
            [
                'name' => 'Ana Martínez',
                'email' => 'ana.martinez@colegio.com',
                'password' => bcrypt('profesor123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'tipo' => 'profesor'
            ],
            [
                'name' => 'Pedro Sánchez',
                'email' => 'pedro.sanchez@colegio.com',
                'password' => bcrypt('profesor123'),
                'email_verified_at' => now(),
                'remember_token' => Str::random(10),
                'tipo' => 'profesor'
            ]
        ];

        foreach ($profesores as $profesor) {
            User::create($profesor);
        }

        User::create([
            'name' => 'Admin Gonzaga',
            'username' => 'admin',
            'email' => 'admin@colegio.com',
            'password' => bcrypt('admin123'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'tipo' => 'admin'
        ]);

        User::create([
            'name' => 'Coordinador',
            'username' => 'coordinador',
            'email' => 'coordinador@colegio.com',
            'password' => bcrypt('coordinador123'),
            'email_verified_at' => now(),
            'remember_token' => Str::random(10),
            'tipo' => 'coordinador'
        ]);
    }
}
