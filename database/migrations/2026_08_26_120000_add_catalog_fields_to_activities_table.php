<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->foreignId('user_id')
                ->nullable()
                ->after('id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('category', 50)->nullable()->after('name');
            $table->enum('location_type', ['home', 'gym', 'outdoor', 'any'])
                ->default('any')
                ->after('category');
            $table->string('video_url', 1000)->nullable()->after('calories');
            $table->boolean('is_premium')->default(false)->after('video_url');

            $table->index(['user_id', 'name']);
            $table->index('location_type');
            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'name']);
            $table->dropIndex(['location_type']);
            $table->dropIndex(['category']);
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['category', 'location_type', 'video_url', 'is_premium']);
        });
    }
};
