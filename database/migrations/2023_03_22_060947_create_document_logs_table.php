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
        Schema::create('document_logs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('document_id');
            $table->bigInteger('from_id');
            $table->bigInteger('to_id');
            $table->bigInteger('action_id');
            $table->bigInteger('acknowledge_id');
            $table->string('comment');
            $table->bigInteger('approved_id');
            $table->bigInteger('rejected_id');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('document_logs');
    }
};
