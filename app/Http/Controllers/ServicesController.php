<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use App\Models\ServiceUser;
use App\Models\Service;
use App\Http\Requests\StoreServiceRequests;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Crypt;

class ServicesController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = Auth::user();

        $servicesUser = $user->services()->paginate(10)->through(fn ($service) => [
            'id' => $service->id,
            'service' => $service->service
        ]);


        $services = Service::all()->map(fn ($service) => [
            'id' => $service->id,
            'service' => $service->service
        ]);

        return Inertia::render('Profile/Services', [
            'servicesUser' => $servicesUser,
            'services' => $services,
        ]);
    }

    public function store(StoreServiceRequests $request)
    {

        $user = Auth::user();

        $token =  Crypt::encryptString($request->token);

        $serviceId = $request->service;

        $user->services()->attach($serviceId, ['token' => $token]);

        return to_route('service.edit');
    }


    /**
     * Delete the user's account.
     */
    public function destroy(Request $request)
    {
        $user = Auth::user();

        $user->services()->detach($request->id);

        return to_route('service.edit');
    }
}
