<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('document_type_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->string('tracking_no')->unique();
            $table->bigInteger('series_no');
            $table->string('received_from');
            $table->bigInteger('category_id')->unsigned();
            $table->text('description');
            $table->date('date_received');
            $table->timestamps();

            $table->foreign('document_type_id')
                ->references('id')
                ->on('document_types')
                ->restrictOnDelete()
                ->restrictOnUpdate();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
};
