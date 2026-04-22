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
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->foreignId('element_id')->nullable()-> Moranconstrained()->onDelete('set null');
            $table->string('label'); // On sauvegarde le nom au cas où l'élément est supprimé plus tard
            $table->integer('quantity');
            $table->decimal('price', 10, 2); // Prix figé au moment de l'achat
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
