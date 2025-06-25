<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Laravel\Prompts\Table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('livestocks', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('type');
            $table->string('code');
            $table->string('live_weight')->nullable();
            $table->string('time_slaughtered')->nullable();
            $table->string('carcass_weight')->nullable();
            $table->string('status')->nullable();
            $table->string('time_dispatched')->nullable();
            // $table->string('meat_inspection_no')->nullable();
            $table->string('remarks')->nullable();
            $table->unsignedBigInteger('owner_id');
            $table->string('batch'); 
            $table->unsignedBigInteger('handler_id');
            $table->unsignedBigInteger('delivery_id');
            $table->unsignedBigInteger('inspector_id')->nullable();
            $table->unsignedBigInteger('order_of_payment_id')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('handler_id')->references('id')->on('handlers')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('delivery_id')->references('id')->on('deliveries')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('inspector_id')->references('id')->on('inspectors')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('owner_id')->references('id')->on('owners')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('livestocks');
    }
};
