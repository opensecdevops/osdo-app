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
        Schema::create('package_stats', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('package_version_id')->unsigned();
            $table->timestamps();
        });

        Schema::table('package_stats', function (Blueprint $table) {
            $table->foreign('package_version_id')->references('id')->on('package_versions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('package_stats');
    }
};
