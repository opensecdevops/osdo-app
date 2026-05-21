<?php

namespace App\Http\Controllers\Cli;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageVersion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class CliPackageController extends Controller
{
    /**
     * Listar paquetes del usuario autenticado con su última versión.
     * Responde con JSON limpio (no Inertia).
     */
    public function index(Request $request): JsonResponse
    {
        $packages = Package::with(['versions' => function ($query) {
            $query->latest()->limit(1);
        }])
        ->where('user_id', $request->user()->id)
        ->where('status', 1) // solo paquetes successful
        ->get()
        ->map(function (Package $package) {
            $latestVersion = $package->versions->first();
            return [
                'id'          => $package->id,
                'name'        => $package->name,
                'description' => $package->description,
                'type'        => $package->type === 1 ? 'infrastructure' : 'pipeline',
                'repository'  => $package->repository,
                'version'     => $latestVersion?->version,
                'version_id'  => $latestVersion?->id,
                'commit'      => $latestVersion?->commit,
            ];
        });

        return response()->json(['data' => $packages]);
    }

    /**
     * Obtener detalle de un paquete: config.json + templates.
     * Esto es lo que el CLI necesita para renderizar con Handlebars.
     */
    public function show(Request $request, int $id): JsonResponse
    {
        $package = Package::with('versions')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        // Usar la versión más reciente o la especificada
        $versionId = $request->query('version_id');
        $version = $versionId
            ? $package->versions()->findOrFail($versionId)
            : $package->versions()->latest()->firstOrFail();

        // Construir la ruta de storage
        $serviceName = $package->service?->service ?? 'gitlab';
        $basePath = "packages/{$serviceName}/{$package->name}/{$version->commit}";

        if (! Storage::exists("{$basePath}/config.json")) {
            return response()->json(['error' => 'Paquete no encontrado en storage'], 404);
        }

        $form = json_decode(Storage::get("{$basePath}/config.json"), true);

        // Cargar los templates Handlebars
        $templates = [];
        $templateFiles = Storage::files("{$basePath}/templates");
        foreach ($templateFiles as $templateFile) {
            $fileName = pathinfo($templateFile, PATHINFO_FILENAME);
            $templates[] = [
                'file'    => $fileName,
                'content' => Storage::get($templateFile),
            ];
        }

        return response()->json([
            'package'    => [
                'id'         => $package->id,
                'name'       => $package->name,
                'type'       => $package->type === 1 ? 'infrastructure' : 'pipeline',
                'version'    => $version->version,
                'version_id' => $version->id,
                'commit'     => $version->commit,
            ],
            'form'       => $form,
            'templates'  => $templates,
        ]);
    }

    /**
     * Descargar el paquete completo como ZIP.
     * config.json + templates/ empaquetados para el CLI.
     */
    public function download(Request $request, int $id): Response|JsonResponse
    {
        $package = Package::where('user_id', $request->user()->id)
            ->findOrFail($id);

        $versionId = $request->query('version_id');
        $version = $versionId
            ? $package->versions()->findOrFail($versionId)
            : $package->versions()->latest()->firstOrFail();

        $serviceName = $package->service?->service ?? 'gitlab';
        $basePath = storage_path("app/packages/{$serviceName}/{$package->name}/{$version->commit}");

        if (! is_dir($basePath)) {
            return response()->json(['error' => 'Archivos del paquete no encontrados'], 404);
        }

        // Crear ZIP temporal
        $zipPath = tempnam(sys_get_temp_dir(), 'osdo-package-') . '.zip';
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Agregar config.json
        if (file_exists("{$basePath}/config.json")) {
            $zip->addFile("{$basePath}/config.json", 'config.json');
        }

        // Agregar README.md
        if (file_exists("{$basePath}/README.md")) {
            $zip->addFile("{$basePath}/README.md", 'README.md');
        }

        // Agregar templates/
        $templatesDir = "{$basePath}/templates";
        if (is_dir($templatesDir)) {
            foreach (glob("{$templatesDir}/*.twig") as $templateFile) {
                $zip->addFile($templateFile, 'templates/' . basename($templateFile));
            }
        }

        $zip->close();

        $safeName = str_replace('/', '_', $package->name);
        $filename = "osdo-package-{$safeName}-{$version->version}.zip";

        return response()->download($zipPath, $filename)->deleteFileAfterSend(true);
    }
}
