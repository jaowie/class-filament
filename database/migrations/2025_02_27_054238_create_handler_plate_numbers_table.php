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
        Schema::create('handler_plate_numbers', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->foreignId('handler_id')->constrained()->onDelete('cascade');
            $table->string('plate_no');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handler_plate_numbers');
    }
};
