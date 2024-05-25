<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GeneratorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::whereHas('versions')
            ->with(['versions' => function ($query) {
                $query->latest()->take(1);
            }])
            ->paginate(10)
            ->through(function ($package) {
                $latestVersion = $package->versions->first();

                return [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description,
                    'type' => $package->type === 1 ? 'Infrastructure' : 'CI/CD',
                    'license' => $package->license,
                    'version' => $latestVersion ? $latestVersion->version : '',
                ];
            });

        return Inertia::render('Generator/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($packageName, $id)
    {
        $package = Package::where('name', $packageName)->first();

        if (! $package) {
            throw new NotFoundHttpException('Package not found');
            //return response()->json(['error' => 'Package not found'], 404);
        }

        $version = $package->versions()->where('id', $id)->first();

        if (! $version) {
            throw new NotFoundHttpException('Version not found');
            //return response()->json(['error' => 'Version not found'], 404);
        }

        $service = $package->service()->first();

        $form = Storage::get(sprintf('packages/%s/%s/%s/config.json', $service->service, $packageName, $version->commit));

        //Get content of the templates folder and create array with the files, name file without extension and content
        $templates = Storage::files(sprintf('packages/%s/%s/%s/templates', $service->service, $packageName, $version->commit));
        $templates = array_map(function ($template) {
            return [
                'file' => pathinfo($template, PATHINFO_FILENAME),
                'content' => Storage::get($template),
            ];
        }, $templates);

        return Inertia::render('Generator/Create', [
            'package' => $package,
            'form' => json_decode($form),
            'templates' => $templates,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show($packageName)
    {

        $package = Package::where('name', $packageName)->first();

        if (! $package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        // Verificar si el paquete tiene versiones
        if ($package->versions()->count() == 0) {
            return response()->json(['error' => 'Package has no versions'], 404);
        }

        $service = $package->service()->first();

        $versions = $package->versions()->get()->map(function ($version) use ($packageName, $service) {
            return [
                'id' => $version->id,
                'version' => $version->version,
                'description' => $version->description,
                'commit' => $version->commit,
                'created_at' => $version->created_at->format('d/m/Y'),
                'readme' => Str::markdown(Storage::get(sprintf('packages/%s/%s/%s/README.md', $service->service, $packageName, $version->commit))),
            ];
        });

        $user = $package->user()->first();

        return Inertia::render('Generator/Show', [
            'package' => $package,
            'service' => $service,
            'versions' => $versions,
            'user' => $user,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        //
    }
}
