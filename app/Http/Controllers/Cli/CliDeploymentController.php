<?php

namespace App\Http\Controllers\Cli;

use App\Http\Controllers\Controller;
use App\Models\Deployment;
use App\Models\PackageVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CliDeploymentController extends Controller
{
    /**
     * Listar deployments del usuario.
     */
    public function index(Request $request): JsonResponse
    {
        $deployments = Deployment::with(['packageVersion.package'])
            ->where('user_id', $request->user()->id)
            ->latest()
            ->paginate(20);

        return response()->json($deployments);
    }

    /**
     * Registrar un nuevo deployment desde el CLI.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'package_version_id' => ['required', 'integer', 'exists:package_versions,id'],
            'platform'           => ['required', 'string', 'in:kubernetes,k3s,docker-compose,docker-swarm,helm'],
            'namespace'          => ['nullable', 'string', 'max:255'],
            'environment'        => ['nullable', 'string', 'max:100'],
            'metadata'           => ['nullable', 'array'],
        ]);

        // Verificar que la versión pertenece al usuario
        $version = PackageVersion::whereHas('package', function ($query) use ($request) {
            $query->where('user_id', $request->user()->id);
        })->findOrFail($validated['package_version_id']);

        $deployment = Deployment::create([
            ...$validated,
            'user_id' => $request->user()->id,
            'status'  => 'pending',
        ]);

        return response()->json([
            'data'    => $deployment,
            'message' => 'Deployment registrado correctamente.',
        ], 201);
    }

    /**
     * Actualizar el status de un deployment desde el CLI.
     * Llamado por `osdo app push --deployment-id <id> --status deployed`
     */
    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $deployment = Deployment::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $validated = $request->validate([
            'status'  => ['required', 'string', 'in:pending,deploying,deployed,failed'],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        $deployment->update([
            'status'      => $validated['status'],
            'message'     => $validated['message'] ?? $deployment->message,
            'deployed_at' => $validated['status'] === 'deployed' ? now() : $deployment->deployed_at,
        ]);

        return response()->json([
            'data'    => $deployment->fresh(),
            'message' => "Status actualizado a '{$validated['status']}'.",
        ]);
    }
}
