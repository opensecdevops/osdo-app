<?php

namespace App\Http\Controllers;

use App\Models\Package;
use App\Models\PackageStats;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function show()
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

        $packageGenerate = PackageStats::count();

        return Inertia::render('Dashboard', [
            'packages' => $packages,
            'packageGenerate' => $packageGenerate,
        ]);

    }
}
