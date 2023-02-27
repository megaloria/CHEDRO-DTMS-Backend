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
        Schema::create('heis', function (Blueprint $table) {
            $table->id();
            $table->string('uii');
            $table->string('name');
            $table->string('street_barangay');
            $table->string('city_municipality');
            $table->string('province');
            $table->string('head_of_institution');
            $table->string('email');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heis');
    }
};
