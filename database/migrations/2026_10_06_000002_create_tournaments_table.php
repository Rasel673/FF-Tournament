<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('map');
            $table->unsignedInteger('entry_fee')->default(0);
            $table->unsignedInteger('prize')->default(0);
            $table->unsignedInteger('per_kill')->default(0);
            $table->unsignedInteger('slots')->default(50);
            $table->dateTime('start_time');
            $table->dateTime('open_time')->nullable();
            $table->string('status')->default('upcoming'); // upcoming | active | completed
            $table->string('room_id')->nullable();
            $table->string('room_password')->nullable();
            $table->string('proof_image')->nullable();
            $table->text('result_note')->nullable();
            $table->timestamps();
        });

        Schema::create('registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['tournament_id', 'user_id']);
        });

        Schema::create('results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tournament_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('position')->nullable();
            $table->unsignedInteger('kills')->default(0);
            $table->unsignedInteger('prize')->default(0);
            $table->timestamps();
            $table->unique(['tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('results');
        Schema::dropIfExists('registrations');
        Schema::dropIfExists('tournaments');
    }
};
