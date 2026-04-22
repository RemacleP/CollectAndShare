<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->integer('year_production')->nullable()->after('description');
            $table->text('history')->nullable()->after('year_production');
            $table->string('condition')->nullable()->after('image');
            $table->boolean('is_for_trade')->default(false)->after('condition');
            $table->boolean('is_for_sale')->default(false)->after('is_for_trade');
        });
    }

    public function down(): void
    {
        Schema::table('elements', function (Blueprint $table) {
            $table->dropColumn(['year_production', 'history', 'condition', 'is_for_trade', 'is_for_sale']);
        });
    }
};
