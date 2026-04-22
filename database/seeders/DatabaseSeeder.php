<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Club;
use App\Models\Role;
use App\Models\Address;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Création des Rôles
        $roles = [
            ['name' => 'admin', 'label' => 'Administrateur'],
            ['name' => 'responsable', 'label' => 'Responsable'],
            ['name' => 'member', 'label' => 'Membre'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['name' => $role['name']], $role);
        }

        $roleAdmin = Role::where('name', 'admin')->first();
        $roleMember = Role::where('name', 'member')->first();

        // 2. Création de ton compte Admin (Pascal)
        $admin = User::create([
            'username' => 'SuperAdmin',
            'firstname' => 'Pascal',
            'lastname' => 'Admin',
            'email' => 'admin@admin.be',
            'is_admin' => true,
            'password' => Hash::make('Pascal01'),
            'email_verified_at' => now(),
        ]);

        // Adresse de l'admin (Type: Personnel)
        $admin->address()->create([
            'type' => 'primary',
            'street' => 'Rue de la Loi',
            'number' => '16',
            'postal_code' => '1000',
            'city' => 'Bruxelles',
            'country' => 'Belgique',
        ]);

        // 3. Création des Clubs
        $clubsData = [
            ['name' => 'Cercle Numismatique de Messancy', 'city' => 'Messancy', 'pc' => '6780'],
            ['name' => 'Les Collectionneurs de l\'Arlonais', 'city' => 'Arlon', 'pc' => '6700'],
            ['name' => 'Phila-Club Luxembourg', 'city' => 'Aubange', 'pc' => '6790'],
        ];

        foreach ($clubsData as $data) {
            $club = Club::create([
                'name' => $data['name'],
                'description' => 'Un club passionnant pour partager nos découvertes.',
                'email' => 'contact@' . strtolower(str_replace(' ', '', $data['city'])) . '.be',
            ]);

            // Ajout de l'adresse du club (Type: Siège Social)
            $club->address()->create([
                'type' => 'primary',
                'street' => 'Rue de la Station',
                'number' => rand(1, 50),
                'postal_code' => $data['pc'],
                'city' => $data['city'],
                'country' => 'Belgique',
            ]);
        }

        $allClubs = Club::all();

        // 4. Création des utilisateurs (10 membres)
        for ($i = 1; $i <= 10; $i++) {
            $user = User::create([
                'username' => 'User' . $i,
                'firstname' => 'Prénom' . $i,
                'lastname' => 'Nom' . $i,
                'email' => 'user' . $i . '@user.be',
                'password' => Hash::make('Pascal01'),
                'email_verified_at' => now(),
            ]);

            // Adresse de l'utilisateur (Type: Domicile)
            $user->address()->create([
                'type' => 'primary',
                'street' => 'Rue des Collectionneurs',
                'number' => $i,
                'postal_code' => '6700',
                'city' => 'Arlon',
                'country' => 'Belgique',
            ]);

            // Liaison au club
            $user->clubs()->attach($allClubs->random()->id, [
                'role_id' => $roleMember->id
            ]);
        }

        // On donne un rôle admin à Pascal dans le premier club
        $admin->clubs()->attach($allClubs->first()->id, [
            'role_id' => $roleAdmin->id
        ]);

        foreach ($allClubs as $club) {
            \App\Models\Conversation::create([
                'club_id' => $club->id,
                'title' => 'Général',
                'slug' => 'general-' . $club->id,
                'is_private' => false
            ]);
        }
    }
}
