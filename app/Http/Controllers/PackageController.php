<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\VerifyPackage;
use App\Jobs\RetrievePackage;
use App\Models\Package;
use App\Models\User;
use App\Traits\ValidationTrait;
use Exception;
use GrahamCampbell\GitLab\Facades\GitLab;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use ZipArchive;

class PackageController extends Controller
{
    use ValidationTrait;

    private $storagePath;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = Auth::user();

        $packages = $user->packages()->paginate(10)->through(fn ($package) => [
            'id' => $package->id,
            'name' => $package->name,
            'description' => $package->description,
            'type' => $package->type === 1 ? 'Infrastructure' : 'CI/CD',
            'version' => $package->versions()->latest()->first() ? $package->versions()->latest()->first()->version : '',
        ]);

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Display a listing of the resource.
     */
    public function test()
    {
        return Inertia::render('Packages/Test');
    }

    public function verify(VerifyPackage $request)
    {

        //save zip into tmp folder
        $zipFile = $request->file('package')->store('tmp');
        $this->storagePath = Storage::path('/');
        $zipPath = $this->storagePath.$zipFile;
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            //Set Error to inertiajs
            return Inertia::render('Packages/Test', [
                'errors' => [
                    'package' => 'Can\'t open zip file',
                ],
            ]);
        }

        $folderZip = $zip->getNameIndex(0);
        $zip->extractTo($this->storagePath.'/tmp/');
        $zip->close();
        Storage::delete($zipFile);

        [$isValid, $errorMessage] = $this->validatePackage($folderZip);

        if (! $isValid) {
            Storage::deleteDirectory('tmp/'.$folderZip);

            //Set Error to inertiajs
            return Inertia::render('Packages/Test', [
                'errors' => [
                    'package' => $errorMessage,
                ],
            ]);
        }

        $form = Storage::get(sprintf('tmp/%s/config.json', $folderZip));

        $form = json_decode($form);

        return Inertia::render('Generator/Create', [
            'package' => $form->name,
            'service' => $form->type,
            'version' => $form->version,
            'form' => $form,
        ]);

        /* return Inertia::render('Packages/Test', [
            'flash' => [
                'message' => 'Package is valid',
            ],
        ]);
        */
    }

    public function create(Request $request)
    {
        $user = Auth::user();

        return Inertia::render('Packages/Create', [
            'services' => $user->services()->get()->map(fn ($service) => [
                'id' => $service->id,
                'label' => $service->service,
            ]),
            'projects' => Inertia::lazy(function () use ($user, $request) {
                $serviceSelected = $request->input('params.service', '');
                $service = $user->services()->where('service_id', $serviceSelected)->first();

                $token = Crypt::decryptString($service->pivot->token);
                config(['gitlab.connections.main.token' => $token]);

                $search = $request->input('params.search', '');

                try {
                    return GitLab::projects()->all(['owned' => true, 'search' => $search, 'visibility' => 'public']);
                } catch (\Throwable $th) {
                }
            }),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackageRequest $request)
    {
        $package = new Package();

        $package->repository_id = $request->id;
        $package->user_id = Auth::id();
        $package->service_id = $request->service;
        $package->repository = $request->repository;
        $package->type = $request->type;
        $package->name = $request->name;

        $package->save();

        RetrievePackage::dispatchAfterResponse($package);

        return to_route('packages.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        //
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
    public function destroy(Request $request, $packageId)
    {
        //Package delete only by owner
        $package = Package::find($packageId);

        if (! $package) {
            return response()->json(['message' => 'Package not found'], 404);
        }

        if ($package->user_id != Auth::id()) {
            return response()->json(['message' => 'You can\'t pass!!!!'], 403);
        }

        $service = $package->service()->first();

        $folderPackage = sprintf('packages/%s/%s', $service->service, $package->name);

        Storage::deleteDirectory($folderPackage);
        $package->delete();

        return to_route('packages.index');
    }

    public function editor($packageName)
    {

        $package = Package::where('name', $packageName)->first();

        if ($package->user_id != Auth::id()) {
            return response()->json(['message' => 'You can\'t pass!!!!'], 403);
        }

        $user = User::find($package->user_id);
        $service_id = $package->service_id;
        $service = $user->services()->where('service_id', $service_id)->first();
        $token = $service->pivot->token;
        $token = Crypt::decryptString($token);

        if ($service_id == 1) {
            config(['gitlab.connections.main.token' => $token]);

            $commits = GitLab::repositories()->commits($package->repository_id);

            if (! empty($commits)) {
                $shaCommit = $commits[0]['id'];
                $shaSortCommit = $commits[0]['short_id'];

                $files = GitLab::repositories()->archive($package->repository_id, ['sha' => $shaCommit], 'zip');
                $finalPathZip = sprintf('tmp/%s/%s/%s.zip', $service_id, $package->id, $shaSortCommit);

                Storage::put($finalPathZip, $files);
                $zipPath = Storage::path($finalPathZip);
                $zip = new ZipArchive;

                if ($zip->open($zipPath) !== true) {
                    new Exception('Not open zip');
                }

                $folderZip = $zip->getNameIndex(0);
                $storagePath = Storage::path('/');
                $extractPath = sprintf('%stmp/%s/%s', $storagePath, $service_id, $package->id);
                $zip->extractTo($extractPath);
                $zip->close();
            }
        }

        $storagePath = Storage::path('/');

        $extractPath = sprintf('%stmp/%s/%s', $storagePath, $service_id, $package->id);

        //$folderZip = 'laravel-inertiajs-ci-for-gitlab-f596a54231b5d33cd666574dee32b5c1c3fd2ae0-f596a54231b5d33cd666574dee32b5c1c3fd2ae0';
        // dd($extractPath.'/'. $folderZip);

        $files = File::allFiles($extractPath.'/'.$folderZip);

        $folderStructure = [];

        foreach ($files as $file) {
            $relativePath = $file->getRelativePath();
            $paths = explode('/', $relativePath);
            $this->placeFile($folderStructure, $paths, [
                'name' => $file->getFilename(),
                'path' => $relativePath,
                'size' => $file->getSize(),
                'type' => 'file',
                'extension' => $file->getExtension(),
                'content' => $file->isFile() ? File::get($file->getPathname()) : null,
            ]);
        }

        return Inertia::render('Packages/Editor', [
            'package' => $package,
            'service' => $service,
            'commits' => $commits,
            'shaCommit' => $shaCommit,
            'shaSortCommit' => $shaSortCommit,
            'folderZip' => $folderZip,
            'structure' => $folderStructure,
        ]);
    }

    private function placeFile(&$structure, $paths, $fileInfo)
    {
        if (empty($paths) || $paths[0] == '') {
            $structure[] = $fileInfo;

            return;
        }

        $currentFolder = array_shift($paths);

        if (! isset($structure[$currentFolder])) {
            $structure[$currentFolder] = [
                'name' => $currentFolder,
                'type' => 'folder',
                'elements' => [],
            ];
        }

        $this->placeFile($structure[$currentFolder]['elements'], $paths, $fileInfo);
    }
}
