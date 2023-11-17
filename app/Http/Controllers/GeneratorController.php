<?php

namespace App\Http\Controllers;

use App\Http\Requests\GenerateRequests;
use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use App\Utils\TemplateEngine;

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
    public function create($vendor, $packageName, $id)
    {

        $package = Package::where('name', $vendor . '/' . $packageName)->first();

        if (!$package) {
            throw new NotFoundHttpException('Package not found');
            //return response()->json(['error' => 'Package not found'], 404);
        }


        $version = $package->versions()->where('id', $id)->first();

        if (!$version) {
            throw new NotFoundHttpException('Version not found');
            //return response()->json(['error' => 'Version not found'], 404);
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

        $config = Storage::get($routePackage . '/config.json');

        $config = json_decode($config, true);


        $storagePackagePath = app_path() . '/../storage/app/' . $routePackage . 'templates';



        $data = $request->data;

        foreach ($config['blocks'] as $block) {
            if (in_array($block['template'], $blocksGenerate)) {
                foreach ($block['fields'] as $field) {
                    if (isset($field['type']) && $field['type'] === 'select') {
                        $options = array_column($field['options'], 'value', 'id');
                        $selectedOptionId = $data[$block['template']][$field['name']];
                        $selectedOptionValue = $options[$selectedOptionId] ?? null;
                        if ($selectedOptionValue !== null) {
                            $data[$block['template']][$field['name'] . '_value'] = $selectedOptionValue;
                        }
                    }
                }
            }
        }

        $generate[] = $this->generateFile($storagePackagePath, $config, $data, $blocksGenerate);


        foreach ($config['blocks'] as $block) {
            if (in_array($block['template'], $blocksGenerate)) {
                if (isset($block['extra'])) {
                    foreach ($block['extra'] as $extra) {
                        if (!isset($extra['dependencies'])) {
                            $generate[] = $this->generateFile($storagePackagePath, $extra, $data, $blocksGenerate);
                        } else {
                            foreach ($extra['dependencies'] as $dependency) {
                                if (in_array($dependency, $blocksGenerate)) {
                                    $generate[] = $this->generateFile($storagePackagePath, $extra, $data, $blocksGenerate);
                                }
                            }
                        }
                    }
                }
            }
        }

        return Inertia::render('Generator/Create', [
            'generate' => $generate
        ]);
    }

    private function generateFile($path, $config, $data, $blocksGenerate)
    {

       view::addLocation($path);
       
        $generate = [
            'file' => $config['file'],
            'language' => $config['language'],
            'view' => view::make( $config['template'], ['config' => $config, 'data' => $data, 'blocksGenerate' => $blocksGenerate])->render()
        ];

        return $generate;
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

        // Verificar si el paquete tiene versiones
        if ($package->versions()->count() == 0) {
            return response()->json(['error' => 'Package has no versions'], 404);
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
