<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('references', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('ref_code')->unique();
            $table->string('title')->nullable();

            // Better data types
            $table->char('month', 2)->nullable();
            $table->unsignedSmallInteger('year')->nullable();

            $table->enum('status', ['active', 'inactive', 'pending'])->default('pending');

            $table->timestamps();

            // Index for faster report filtering
            $table->index(['user_id', 'month', 'year']);
        });

        Schema::create('reference_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('reference_id')
                ->constrained('references')
                ->cascadeOnDelete();

            // Personal Information
            $table->string('company')->nullable();
            $table->string('name')->nullable();
            $table->string('category')->nullable();
            $table->string('position')->nullable();
            $table->string('nationality')->nullable();
            $table->string('gender')->nullable();
            $table->string('domicile')->nullable();
            $table->string('status')->nullable();

            // Temporary Address
            $table->text('tem_res_add')->nullable();
            $table->string('tem_province')->nullable();
            $table->string('tem_mun_brgy')->nullable();

            // Permanent Address
            $table->text('per_res_add')->nullable();
            $table->string('per_province')->nullable();
            $table->string('per_mun_brgy')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reference_details');
        Schema::dropIfExists('references');
    }
};
