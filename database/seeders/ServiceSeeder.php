<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $services = [
            ['id' => 1, 'service' => 'gitlab']
        ];

        $servicesDB = \App\Models\Service::all();

        if($servicesDB->count() == 0) {
            \App\Models\Service::insert($services);
            return;
        }

        foreach($services as $service) {
            $serviceDB = $servicesDB->where('service', $service['service'])->first();
            if($serviceDB == null) {
                \App\Models\Service::insert($service);
                Log::info('ServiceSeeder: Service ' . $service['service'] . ' inserted');
            } else {
                $serviceDB->update($service);
                Log::info('ServiceSeeder: Service ' . $service['service'] . ' updated');
            }
        }
        
    }
}
