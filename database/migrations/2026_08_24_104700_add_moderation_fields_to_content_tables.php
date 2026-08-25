<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = ['user_recepies', 'restaurants', 'trainer_users'];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->enum('moderation_status', ['pending', 'approved', 'rejected'])
                    ->default('approved')
                    ->after('id');
                $table->timestamp('moderated_at')->nullable();
                $table->text('moderation_comment')->nullable();
                $table->index('moderation_status');
            });
        }
    }

    public function down(): void
    {
        foreach (['user_recepies', 'restaurants', 'trainer_users'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropIndex(['moderation_status']);
                $table->dropColumn(['moderation_status', 'moderated_at', 'moderation_comment']);
            });
        }
    }
};
