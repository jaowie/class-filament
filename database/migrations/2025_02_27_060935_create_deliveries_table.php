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
        Schema::create('deliveries', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('handler_id');
            $table->string('origin');
            $table->date('delivered_at');
            $table->string('time_delivered');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('handler_id')->references('id')->on('handlers')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('deliveries');
    }
};
