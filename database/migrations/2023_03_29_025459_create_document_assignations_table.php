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
        Schema::create('document_assignations', function (Blueprint $table) {
            $table->id();
              $table->bigInteger('document_id')->unsigned()->nullable();
            $table->bigInteger('assigned_id')->unsigned()->nullable();
            $table->timestamps();

             $table->foreign('document_id')
                ->references('id')
                ->on('documents')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('assigned_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_assignations');
    }
};
