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
        Schema::create('internal_mails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('receiver_id')->constrained('users')->onDelete('cascade');

            $table->string('subject');
            $table->text('body');

            // Pour lier à tes demandes d'inscription ou autres objets sans polluer le body
            $table->string('reference_type')->nullable(); // Ex: 'App\Models\RegistrationRequest'
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->timestamp('read_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registration_internal_mails');
    }
};
