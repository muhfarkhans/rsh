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
        Schema::create('client_visit_cuppings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_visit_id');
            $table->unsignedBigInteger('therapy_id');
            $table->string('cupping_type');
            $table->integer('temperature');
            $table->string('blood_pressure');
            $table->integer('pulse');
            $table->integer('respiratory');
            $table->text('side_effect');
            $table->text('first_action');
            $table->text('education_after');
            $table->text('subjective');
            $table->text('objective');
            $table->text('analysis');
            $table->text('planning');
            $table->json('points');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_visit_cuppings');
    }
};
