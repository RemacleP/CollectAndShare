<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            // Description du futur club
            $table->text('club_description')->nullable()->after('new_club_name');

            // Champs d'adresse pour le futur club
            $table->string('street')->nullable()->after('club_description');
            $table->string('number')->nullable()->after('street');
            $table->string('postal_code')->nullable()->after('number');
            $table->string('city')->nullable()->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('registration_requests', function (Blueprint $table) {
            $table->dropColumn(['club_description', 'street', 'number', 'postal_code', 'city']);
        });
    }
};
