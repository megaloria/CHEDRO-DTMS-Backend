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
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('received_from');
            $table->bigInteger('sender_id')->unsigned()->after('series_no');

            $table->foreign('sender_id')
                ->references('id')
                ->on('senders')
                ->restrictOnDelete()
                ->restrictOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('received_from')->after('series_no');
            $table->dropForeign(['sender_id']);
        });
    }
};
