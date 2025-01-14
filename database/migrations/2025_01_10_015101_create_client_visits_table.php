<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('client_visits', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('created_by');
            $table->string('complaint');
            $table->json('medical_history');
            $table->json('family_medical_history');
            $table->json('medication_history');
            $table->json('sleep_habits');
            $table->json('exercise');
            $table->json('nutrition');
            $table->json('spiritual');
            $table->text('diagnose');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_visits');
    }
};
