<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            // On ajoute la colonne 'type' après 'id' ou au début
            // On la met en 'nullable' car l'adresse principale n'a pas forcément de type
            $table->string('type')->nullable()->after('id');

            // Optionnel : on ajoute un index pour accélérer les requêtes de filtrage
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
