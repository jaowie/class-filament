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
        Schema::create('account_codes', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('account_code')->index();    
            $table->string('description');
            $table->string('hog_amount')->nullable();
            $table->string('cattle_amount')->nullable();
            $table->string('goat_amount')->nullable();
            $table->string('amount')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_codes');
    }
};
