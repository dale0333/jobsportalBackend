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
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Personal Information
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('position')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender')->nullable();
            $table->string('domicile')->nullable();
            $table->string('status')->nullable();

            $table->string('tem_res_add')->nullable();
            $table->string('tem_province')->nullable();
            $table->string('tem_mun_brgy')->nullable();

            $table->string('per_res_add')->nullable();
            $table->string('per_province')->nullable();
            $table->string('per_mun_brgy')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('references');
    }
};
