<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
    $roles = ['admin', 'profesor', 'coordinador', 'secretaria'];
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
            }
        }

        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $user->roles()->detach();

            if ($user->tipo === 'admin') {
                $user->assignRole('admin');
            } elseif ($user->tipo === 'profesor') {
                $user->assignRole('profesor');
            } elseif ($user->tipo === 'coordinador') {
                $user->assignRole('coordinador');
            } elseif ($user->tipo === 'secretaria') {
                $user->assignRole('secretaria');
            }
        }
    }
}
