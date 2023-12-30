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
        Schema::create('packs', function (Blueprint $table) {
            $table->id();
            $table->string('pack_token')->unique();
            $table->string('pack_category');
            $table->string('pack_name');
            $table->string('pack_price');
            $table->text('pack_description');
            $table->string('pack_specs');
            $table->enum('available', ['true', 'false'])->default('true');
            $table->enum('pack_level' , ['3' , '2' , '1'])->default('3');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packs');
    }
};
