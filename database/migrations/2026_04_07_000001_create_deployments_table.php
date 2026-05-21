<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('deployments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('package_version_id')->constrained()->onDelete('cascade');
            $table->string('platform')->default('kubernetes'); // kubernetes, k3s, docker-compose, docker-swarm, helm
            $table->string('namespace')->nullable();
            $table->string('environment')->nullable(); // staging, production, etc.
            $table->enum('status', ['pending', 'deploying', 'deployed', 'failed'])->default('pending');
            $table->text('message')->nullable();
            $table->json('metadata')->nullable(); // cli_version, host, osdo_config, etc.
            $table->timestamp('deployed_at')->nullable();
            $table->timestamps();

            // Índices para consultas frecuentes del CLI
            $table->index(['user_id', 'status']);
            $table->index('package_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('deployments');
    }
};
