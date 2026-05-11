<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Supprime l'admin existant pour éviter les doublons si tu relances le seeder
        User::where('email', 'akoua7146@gmail.com')->delete();

        User::create([
            'id'         => (string) Str::uuid(),
            'nom'        => 'AKOUA',
            'prenom'     => 'Directeur',
            'email'      => 'akoua7146@gmail.com',
            'role'       => 'ADMIN',
            'password'   => Hash::make('lea@2000'), // Hash du mot de passe 'lea@2000'
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}

// database/seeders/MedicalProSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\MedicalPro;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MedicalProSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Médecin en attente
        $pro1Id = (string) Str::uuid();
        User::create([
            'id'         => $pro1Id,
            'nom'        => 'DUPONT',
            'prenom'     => 'Jean',
            'email'      => 'jean.dupont@pro.com',
            'role'       => 'PRO',
            'password'   => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        MedicalPro::create([
            'id'            => $pro1Id,
            'specialite'    => 'Gynécologue',
            'matricule'     => 'GYYN001',
            'is_verified'   => false, // En attente
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);

        // Médecin déjà validé
        $pro2Id = (string) Str::uuid();
        User::create([
            'id'         => $pro2Id,
            'nom'        => 'DURAND',
            'prenom'     => 'Marie',
            'email'      => 'marie.durand@pro.com',
            'role'       => 'PRO',
            'password'   => Hash::make('password123'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        MedicalPro::create([
            'id'            => $pro2Id,
            'specialite'    => 'Sage-Femme',
            'matricule'     => 'SFM002',
            'is_verified'   => true, // Validé
            'created_at'    => now(),
            'updated_at'    => now(),
        ]);
    }
}