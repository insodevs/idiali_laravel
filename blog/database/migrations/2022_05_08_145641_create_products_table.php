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
        Schema::create('products', function (Blueprint $table) {
            //$table->foreignId('id')->unique();
            $table->foreignUuid('uuid')->unique();
            $table->string('code', 16);
            //$table->foreignId('group_id');
            //$table->integer('quantity');
            $table->string('name', 255);
            //$table->string('vendor', 100);
            //$table->string('country_of_origin', 100);
            $table->string('description', 10000)->nullable();
            //$table->string('sales_notes', 100);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
};
