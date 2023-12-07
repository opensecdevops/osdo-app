<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StorePackageRequest;
use App\Http\Requests\VerifyPackage;
use App\Jobs\RetrievePackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use GrahamCampbell\GitLab\Facades\GitLab;
use Illuminate\Support\Facades\Storage;
use App\Traits\ValidationTrait;
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
        $zipPath = $request->file('package')->store('tmp');
        $this->storagePath = Storage::path('/');
        $zipPath = $this->storagePath  . $zipPath;
        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== TRUE) {
            //Set Error to inertiajs
            return Inertia::render('Packages/Test', [
                'errors' => [
                    'package' => 'Can\'t open zip file',
                ],
            ]);
        }

        $folderZip = $zip->getNameIndex(0);
        $zip->extractTo($this->storagePath . '/tmp/');
        $zip->close();
        list($isValid, $errorMessage) = $this->validatePackage($folderZip);
        if(!$isValid) {
            //Set Error to inertiajs
            return Inertia::render('Packages/Test', [
                'errors' => [
                    'package' => $errorMessage,
                ],
            ]);
        }
        return Inertia::render('Packages/Test', [
            'success' => true,
        ]);
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

        if (!$package) {
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
}
