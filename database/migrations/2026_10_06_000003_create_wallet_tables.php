<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name');                 // bKash, Nagad, Upay, Rocket
            $table->string('account_number');       // number users send money to
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            // deposit | withdraw | tournament_entry | prize | bonus | penalty
            $table->string('type');
            $table->unsignedInteger('amount');      // always positive, sign comes from type
            $table->string('method')->nullable();
            $table->string('trx_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('screenshot')->nullable();
            $table->string('status')->default('pending'); // pending | approved | rejected
            $table->foreignId('tournament_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('payment_methods');
    }
};
