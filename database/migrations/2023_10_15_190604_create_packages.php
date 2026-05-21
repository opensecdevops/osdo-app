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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('repository_id')->unsigned();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('service_id')->unsigned();
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('license')->nullable();
            $table->string('repository');
            $table->string('homepage')->nullable();
            $table->integer('type');
            // 1: Infra; 2: Pipeline
            $table->string('message')->default('pending');
            // 0: pending, 1: success, 2: error, 3: processing
            $table->integer('status')->default(0);
            $table->timestamps();

            $table->unique(['repository_id', 'user_id', 'service_id']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('service_id')->references('id')->on('services');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
