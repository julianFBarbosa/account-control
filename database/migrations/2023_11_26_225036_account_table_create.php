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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreign('user_id')->references('id')->on('users')
                ->onDelete('cascade');
            $table->unsignedBigInteger('user_id');
            $table->string('name');
            $table->smallInteger('agency');
            $table->smallInteger('number');
            $table->tinyInteger('digit');
            $table->enum('type', ['COMPANY', 'PERSON']);
            $table->string('social_reason')->nullable();
            $table->string('cnpj')->unique()->nullable();
            $table->string('fantasy_name')->nullable();
            $table->string('cpf')->unique()->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};
