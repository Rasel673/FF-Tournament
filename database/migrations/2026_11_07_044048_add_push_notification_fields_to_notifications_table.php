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
        Schema::table('notifications', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
            $table->string('target')->default('all')->after('user_id'); // all | specific
            $table->string('external_id')->nullable()->after('target');
            $table->json('data')->nullable()->after('small_image');
            $table->string('onesignal_id')->nullable()->after('data');
            $table->integer('recipients_count')->default(0)->after('onesignal_id');
            $table->string('status')->default('sent')->after('recipients_count'); // sent | failed
            $table->text('error_message')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn([
                'target',
                'external_id',
                'data',
                'onesignal_id',
                'recipients_count',
                'status',
                'error_message',
            ]);
        });
    }
};
