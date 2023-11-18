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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('token')->unique();
            $table->string('image1');
            $table->string('image2');
            $table->string('image3')->nullable();
            $table->string('image4')->nullable();
            $table->string('image5')->nullable();
            $table->string('image6')->nullable();
            $table->string('image7')->nullable();
            $table->string('name');
            $table->string('rating');
            $table->integer('purchases');
            $table->integer('views');
            $table->integer('downloads');
            $table->string('status');
            $table->integer('price');
            $table->integer('old_price')->nullable();
            $table->string('description');
            $table->string('tags');
            $table->string('category');
            $table->string('publisher');
            $table->string('last_updated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
