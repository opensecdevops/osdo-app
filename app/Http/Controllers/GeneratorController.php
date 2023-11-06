<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateRequests;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;

class GeneratorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::paginate(10)->through(fn ($package) => [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'type' => $package->type === 1 ? 'Infrastructure' : 'CI/CD',
            'license' => $package->license,
            'version' => $package->versions()->latest()->first()->version,
        ]);

        return Inertia::render('Generator/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create($vendor, $packageName, $id)
    {

        $package = Package::where('name', $vendor . '/' . $packageName)->first();

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        
        $version = $package->versions()->where('id', $id)->first();

        if (!$version) {
            return response()->json(['error' => 'Version not found'], 404);
        }

        $service = $package->service()->first();


        $form = Storage::get(sprintf('packages/%s/%s/%s/%s/config.json', $service->service, $vendor, $packageName, $version->commit));

        return Inertia::render('Generator/Create', [
            'package' => $package,
            'service' => $service,
            'version' => $version,
            'form' => json_decode($form),
        ]);
    }


    public function generate(GenerateRequests $request)
    {

        $routePackage = $request->getPackageRoute();

        $blocksGenerate = $request->getBlocksGenerate();

        $config = Storage::get($routePackage.'/config.json');

        $config = json_decode($config, true);

        View::addLocation(app_path().'/../storage/app/'.$routePackage.'templates');

        return View($config['template'])->render();
    }

    /**
     * Display the specified resource.
     */
    public function show($vendor, $packageName)
    {
        $package = Package::where('name', $vendor . '/' . $packageName)->first();

        if (!$package) {
            return response()->json(['error' => 'Package not found'], 404);
        }

        $service = $package->service()->first();

        $versions = $package->versions()->get()->map(function ($version) use ($vendor, $packageName, $service) {
            return [
                'id' => $version->id,
                'version' => $version->version,
                'description' => $version->description,
                'commit' => $version->commit,
                'created_at' => $version->created_at->format('d/m/Y'),
                'readme' => Str::markdown(Storage::get(sprintf('packages/%s/%s/%s/%s/README.md', $service->service, $vendor, $packageName, $version->commit))),
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
