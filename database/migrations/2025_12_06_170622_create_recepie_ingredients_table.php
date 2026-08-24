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
        Schema::create('recepie_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recepie_id')
                ->nullable()
                ->constrained('recepies')
                ->onDelete('cascade');
            $table->foreignId('user_recepie_id')
                ->nullable()
                ->constrained('user_recepies')
                ->onDelete('cascade');
            $table->string('name');
            $table->string('amount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recepie_ingredients');
    }
};
