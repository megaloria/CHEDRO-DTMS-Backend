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
        Schema::table('users', function (Blueprint $table) {
            $table->bigInteger('role_id')->after('id');
            $table->boolean('is_first_login')->after('remember_token');
            $table->renameColumn('email', 'username');
            $table->dropColumn('email_verified_at');
            $table->dropColumn('name');
        });
            
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table ->dropColumn('role_id');
            $table ->dropColumn('is_first_login');
            $table->renameColumn('username', 'email');
            $table->timestamp('email_verified_at')->nullable();
            $table->string('name');
        });
    }
};
