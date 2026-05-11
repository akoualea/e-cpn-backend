<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('profiles')->updateOrInsert(
            ['email' => 'akoua7146@gmail.com'], // L'email de ton choix
            [
                'id' => (string) Str::uuid(),
                'nom' => 'DIRECTEUR',
                'prenom' => 'Hôpital',
                'role' => 'ADMIN',
                'password' => Hash::make('admin123'), // Ton mot de passe secret
                'created_at' => now(),
            ]
        );
    }
}