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
        Schema::create('package_versions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('package_id')->unsigned();
            $table->string('version');
            $table->string('description');
            $table->string('commit');
            $table->timestamps();
        });

        Schema::table('package_stats', function (Blueprint $table) {
            $table->foreign('package_id')->references('id')->on('packages');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages_versions');
    }
};
