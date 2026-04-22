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
        Schema::create('social_links', function (Blueprint $table) {
            $table->id();
            // Morph : crée linkable_id et linkable_type
            $table->morphs('linkable');

            // Relation avec la plateforme
            $table->foreignId('social_platform_id')
                ->constrained('social_platforms')
                ->onDelete('cascade');

            $table->string('identifier'); // On stocke l'identifiant (ex: "mon.profil")
            $table->string('full_url')->nullable(); // Ou l'URL complète si besoin
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('social_links');
    }
};
