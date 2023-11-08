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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('image');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('owner')->nullable();
            $table->string('technologies')->nullable();
            $table->string('category');
            $table->string('url')->nullable();
            $table->enum('status' , ['finished' , 'progress'])->nullable();
            $table->string('date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
