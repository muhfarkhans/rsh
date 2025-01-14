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
        Schema::create('client_visit_checks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('client_visit_id');
            $table->float('temperature');
            $table->string('blood_pressure');
            $table->integer('pulse');
            $table->integer('respiratory');
            $table->integer('weight');
            $table->integer('height');
            $table->json('other')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_visit_checks');
    }
};
