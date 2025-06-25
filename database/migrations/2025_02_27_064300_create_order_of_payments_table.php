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
        Schema::create('order_of_payments', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->unsignedBigInteger('owner_id');
            $table->string('batch');
            $table->string('account_codes');
            $table->string('encoded_by');
            $table->string('order_of_payment_no');
            $table->string('status');
            $table->string('purpose');
            $table->float('amount');
            $table->string('remarks')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('owner_id')->references('id')->on('owners')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_of_payments');
    }
};
