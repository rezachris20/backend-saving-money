<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFamilyPortofoliosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('family_portofolios', function (Blueprint $table) {
            $table->id();
            $table->integer('family_id');
            $table->integer('portofolio_id');
            $table->integer('target');
            $table->boolean('is_active');
            $table->boolean('is_achievement');
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
        Schema::dropIfExists('family_portofolios');
    }
}
