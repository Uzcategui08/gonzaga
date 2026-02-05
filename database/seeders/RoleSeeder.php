<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run()
    {
        $roles = ['admin', 'profesor', 'coordinador', 'secretaria', 'pedagogia', 'profesor_extracurricular'];
        foreach ($roles as $roleName) {
            if (!Role::where('name', $roleName)->exists()) {
                Role::create(['name' => $roleName]);
            }
        }

        $users = \App\Models\User::all();
        foreach ($users as $user) {
            $baseRoles = ['admin', 'profesor', 'coordinador', 'secretaria', 'pedagogia'];
            $currentRoles = $user->roles()->pluck('name')->all();
            $extraRoles = array_values(array_diff($currentRoles, $baseRoles));

            $roleToAssign = null;
            if ($user->tipo === 'admin') {
                $roleToAssign = 'admin';
            } elseif ($user->tipo === 'profesor') {
                $roleToAssign = 'profesor';
            } elseif ($user->tipo === 'coordinador') {
                $roleToAssign = 'coordinador';
            } elseif ($user->tipo === 'secretaria') {
                $roleToAssign = 'secretaria';
            } elseif ($user->tipo === 'pedagogia') {
                $roleToAssign = 'pedagogia';
            } elseif ($user->tipo === 'extracurricular') {
                $roleToAssign = 'profesor_extracurricular';
            }

            if ($roleToAssign) {
                $user->syncRoles(array_values(array_unique(array_merge([$roleToAssign], $extraRoles))));
            }
        }
    }
}
