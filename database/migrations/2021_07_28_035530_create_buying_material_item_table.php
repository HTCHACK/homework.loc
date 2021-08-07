<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBuyingMaterialItemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('buying_material_item', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('buy_material_id');
            $table->unsignedBigInteger('material_id');
            $table->foreign('buy_material_id')->references('id')->on('buy_material')->onDelete('cascade');
            $table->foreign('material_id')->references('id')->on('materials')->onDelete('cascade');
            $table->string('quantity')->nullable(); 
            $table->string('price')->nullable();
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
        Schema::dropIfExists('buying_material_item');
    }
}
