<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->string('street');
            $table->string('number');
            // Boîte postale
            $table->string('box')->nullable();
            $table->string('postal_code');
            $table->string('city');
            $table->string('country')->default('Belgique');

            // Le système polymorphique : crée addressable_id et addressable_type (Users postale facturation, Clubs)
            $table->morphs('addressable');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
