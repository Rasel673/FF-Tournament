<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('email')->nullable()->change();
            $table->string('phone')->nullable()->unique()->after('name');
            $table->string('uid', 12)->nullable()->unique()->after('phone');
            $table->string('avatar')->nullable();
            $table->unsignedInteger('balance')->default(0);
            $table->boolean('is_admin')->default(false);
            $table->boolean('is_blocked')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'uid', 'avatar', 'balance', 'is_admin', 'is_blocked']);
        });
    }
};
