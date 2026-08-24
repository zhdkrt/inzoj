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
        Schema::create('trainings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trainer_user_id')
                ->constrained('trainer_users')
                ->onDelete('cascade');
            $table->string('name');
            $table->integer('time_amount');
            $table->text('description');
            $table->integer('price');
            $table->time('start_time');
            $table->date('date');
            $table->foreignId('category_id')
                ->constrained('training_categories')
                ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trainings');
    }
};
