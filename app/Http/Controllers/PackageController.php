<?php

namespace App\Http\Controllers;

use App\Models\Package;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\StorePackage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Gitlab;

class PackageController extends Controller
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
            'version' => $package->version,
        ]);

        return Inertia::render('Packages/Index', [
            'packages' => $packages,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Packages/Create');
    }

    public function importCreate() {


        $user = Auth::user();

        $service = $user->services()->where('service_id', 1)->first();

        $token = Crypt::decryptString($service->pivot->token);

        $client = new Gitlab\Client();
        $client->authenticate($service->pivot->token, 'http_token');

        $projects = $client->projects->all();

        return Inertia::render('Packages/Import', [
            'projects' => $projects,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePackage $request)
    {
        $file_path = '';
        dump($request->hasFile('package'));
        if ($request->hasFile('package')) {
            $file_path = $request->file('package')->store('packages');
        }
    
        return to_route('packages.index');
    
    }

    public function importStore(ImportPackage $request)
    {


        
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
    public function destroy(Package $package)
    {
        //
    }
    
}
