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
        Schema::table('registration_requests', function (Blueprint $table) {
            // 1. On renomme 'name' en 'lastname' (pour garder les données existantes si besoin)
            $table->renameColumn('name', 'lastname');

            // 2. On ajoute 'firstname' au début
            $table->string('firstname')->after('id');

            // 3. On ajoute 'username' après l'email
            // On le met en unique pour éviter les doublons dès la demande
            $table->string('username')->unique()->after('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropColumn(['firstname', 'username']);
            $table->renameColumn('lastname', 'name');
        });
    }
};
