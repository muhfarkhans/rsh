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
        Schema::table('client_visit_cuppings', function (Blueprint $table) {
            $table->text('side_effect')->nullable()->change();
            $table->text('first_action')->nullable()->change();
            $table->text('education_after')->nullable()->change();
            $table->text('subjective')->nullable()->change();
            $table->text('objective')->nullable()->change();
            $table->text('analysis')->nullable()->change();
            $table->text('planning')->nullable()->change();
            $table->json('points')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_visit_cuppings', function (Blueprint $table) {
            $table->text('side_effect')->nullable(false)->change();
            $table->text('first_action')->nullable(false)->change();
            $table->text('education_after')->nullable(false)->change();
            $table->text('subjective')->nullable(false)->change();
            $table->text('objective')->nullable(false)->change();
            $table->text('analysis')->nullable(false)->change();
            $table->text('planning')->nullable(false)->change();
            $table->json('points')->nullable(false)->change();
        });
    }
};
