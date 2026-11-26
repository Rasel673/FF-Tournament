<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_moderator')->default(false)->after('is_admin');
            $table->unsignedInteger('moderator_balance')->default(0)->after('balance');
            $table->unsignedInteger('moderator_commission_rate')->nullable()->after('moderator_balance');
        });

        Schema::table('tournaments', function (Blueprint $table) {
            $table->foreignId('moderator_id')->nullable()->after('category_id')->constrained('users')->nullOnDelete();
            $table->unsignedInteger('moderator_commission')->default(50)->after('prize');
            $table->boolean('is_commission_paid')->default(false)->after('moderator_commission');
        });
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('moderator_id');
            $table->dropColumn(['moderator_commission', 'is_commission_paid']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_moderator', 'moderator_balance', 'moderator_commission_rate']);
        });
    }
};
