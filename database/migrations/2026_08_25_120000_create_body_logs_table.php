<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('body_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type', 32);
            $table->decimal('value', 8, 2);
            $table->string('unit', 16)->nullable();
            $table->date('logged_at');
            $table->string('note', 500)->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'type', 'logged_at']);
            $table->index(['user_id', 'type', 'logged_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('body_logs');
    }
};
