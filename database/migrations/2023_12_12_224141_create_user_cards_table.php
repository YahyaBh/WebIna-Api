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
        Schema::create('user_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained(); // Assuming you have a 'users' table for the foreign key
            $table->string('card_name');
            $table->string('card_last_four');
            $table->string('card_number');
            $table->unsignedSmallInteger('exp_month');
            $table->unsignedSmallInteger('exp_year');
            $table->string('cvc');
            $table->boolean('is_default')->default(false);
            $table->string('card_type');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_cards');
    }
};
