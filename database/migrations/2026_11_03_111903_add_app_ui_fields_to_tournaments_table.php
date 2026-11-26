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
        Schema::table('tournaments', function (Blueprint $table) {
            $table->string('match_number')->nullable()->after('id');
            $table->string('type')->default('Solo')->after('map');
            $table->json('prize_pool_distribution')->nullable()->after('prize');
            $table->string('tutorial_video_url')->nullable()->after('proof_image');
            $table->string('rules_title')->nullable()->after('tutorial_video_url');
            $table->text('rules')->nullable()->after('rules_title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn([
                'match_number',
                'type',
                'prize_pool_distribution',
                'tutorial_video_url',
                'rules_title',
                'rules',
            ]);
        });
    }
};
