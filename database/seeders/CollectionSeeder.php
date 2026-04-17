<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Définition des catégories
        $categoriesNames = [
            'Monnaies Royales',
            'Timbres Rares',
            'Cartes Pokémon',
            'Capsules de Bière'
        ];

        foreach ($categoriesNames as $name) {
            // Création de la catégorie avec le slug
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name, 'is_active' => 1]
            );

            // Liaison à l'Admin (ID 11 dans club_user_role)
            // Note: le nom de la colonne est 'club_user_id' selon ton SQL
            DB::table('category_club_user')->updateOrInsert(
                [
                    'category_id' => $category->id,
                    'club_user_id' => 11
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        // 2. Création des collections pour l'Admin
        $collections = [
            [
                'name' => 'Trésors Numismatiques',
                'description' => 'Ma collection de pièces de 5 francs en argent.',
                'cat' => 'Monnaies Royales'
            ],
            [
                'name' => 'Pokémon Wizards',
                'description' => 'Set de base de 1999 complet.',
                'cat' => 'Cartes Pokémon'
            ]
        ];

        foreach ($collections as $col) {
            // Création de la collection
            // Attention : nécessite club_id (obligatoire)
            $newCol = Collection::updateOrCreate(
                ['slug' => Str::slug($col['name'])],
                [
                    'name' => $col['name'],
                    'description' => $col['description'],
                    'club_id' => 1, // Le club de l'admin
                    'club_user_id' => 11, // L'admin lui-même
                ]
            );

            // Liaison Collection <-> Catégorie (Table pivot category_collection)
            $category = Category::where('name', $col['cat'])->first();
            if ($category) {
                // On utilise DB pour être sûr de passer outre les problèmes de modèles
                DB::table('category_collection')->updateOrInsert([
                    'category_id' => $category->id,
                    'collection_id' => $newCol->id
                ]);
            }
        }

        $this->command->info('Seed terminé ! Les colonnes club_user_id et club_id sont correctement remplies.');
    }
}
