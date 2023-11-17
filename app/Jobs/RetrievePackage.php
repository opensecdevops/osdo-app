<?php

namespace App\Jobs;

use App\Models\Package;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use GrahamCampbell\GitLab\Facades\GitLab;
use Illuminate\Support\Facades\Storage;
use ZipArchive;
use Illuminate\Support\Facades\Validator;
use Throwable;
use App\Exceptions\NoRetryException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\View;

class RetrievePackage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $package;

    /**
     * Create a new job instance.
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->package->message = 'processing';
        $this->package->status = 3;
        $this->package->save();

        $zip = new ZipArchive;
        $storagePath = config('filesystems.disks.local.root');
        //get service and user of package
        $user = User::find($this->package->user_id);
        $service_id = $this->package->service_id;
        $service = $user->services()->where('service_id', $service_id)->first();
        $token = $service->pivot->token;
        $token = Crypt::decryptString($token);

        if ($service_id == 1) {
            $this->validatePackage('pipeline-php-0e97821f8cfc3ccfb58d21dae08e39ce1f466f7a-0e97821f8cfc3ccfb58d21dae08e39ce1f466f7a');

            /* $shaSortCommit = '0e97821f';
            $folderPackage = sprintf('packages/%s/%s/%s', $service->service, $this->package->name, $shaSortCommit);
            dump($folderPackage);
            if (!Storage::exists($folderPackage)) {
                Storage::makeDirectory($folderPackage);
            }
            
            $folderZip = 'pipeline-php-0e97821f8cfc3ccfb58d21dae08e39ce1f466f7a-0e97821f8cfc3ccfb58d21dae08e39ce1f466f7a';
            $tmpFolderPackage = sprintf('tmp/%s', $folderZip);
            dump($tmpFolderPackage);

           if(Storage::move($tmpFolderPackage, $folderPackage)) {
                Storage::deleteDirectory($tmpFolderPackage);
            }*/

            /*$this->package->message = 'success';
            $this->package->status = 1;
            $this->package->save();*/
            /* config(['gitlab.connections.main.token' => $token]);
            $project = GitLab::repositories()->archive($this->package->repository_id, ['sha' => '0e97821f8cfc3ccfb58d21dae08e39ce1f466f7a'], 'zip');
            Storage::disk('local')->put('tmp/' . $this->package->id . '.zip', $project);
            $zipPath = $storagePath.'/tmp/' . $this->package->id . '.zip';
            if ($zip->open($zipPath) === TRUE) {

                dump($zip->getNameIndex(0));
                $zip->extractTo($storagePath.'/tmp/');
                $zip->close();
        
            } */
        }
    }

    public function failed(Throwable  $exception)
    {
        if ($exception instanceof NoRetryException) {
            $this->package->message = $exception->getMessage();
            $this->package->status = 2;
            $this->package->save();
            $this->delete();
        }
    }

    private function validatePackage($folder)
    {

        if (!Storage::exists('tmp/' . $folder . '/config.json')) {
            $this->fail(new NoRetryException('Not found config.json'));
        }

        $config = Storage::get('tmp/' . $folder . '/config.json');

        $jsonData = json_decode($config, true);

        $templates = $this->collectTemplates($jsonData);

        $rules = [
            'name' => 'required|string',
            'version' => 'required|string',
            'description' => 'required|string',
            'homepage' => 'nullable|string',
            'repository' => 'nullable|string',
            'type' => 'required|in:pipeline,infrastructure',
            'license' => 'required|string',
            'author' => 'required|string',
            'template' => 'required|string',
            'file' => 'required|string',
            'language' => 'required|string',
            'blocks' => 'required|array',
            'blocks.*.template' => 'required|string',
            'blocks.*.name' => 'required|string',
            'blocks.*.description' => 'nullable|string',
            'blocks.*.fields' => 'required|array',
            'blocks.*.dependencies' => 'nullable|array',
            'blocks.*.dependencies.*' => Rule::in($templates),
            'blocks.*.extra' => 'nullable|array',
            'blocks.*.extra.*.language' => 'required|string',
            'blocks.*.extra.*.file' => 'required|string',
            'blocks.*.extra.*.route' => 'nullable|string',
            'blocks.*.extra.*.template' => 'required|string',
            'blocks.*.extra.*.dependencies' => 'nullable|array',
            'blocks.*.extra.*.dependencies.*' => Rule::in($templates),
            'blocks.*.fields.*.type' => 'required|in:text,switch,select',
            'blocks.*.fields.*.name' => 'required|string',
            'blocks.*.fields.*.label' => 'required|string',
            'blocks.*.fields.*.rules' => 'nullable|string',
            'blocks.*.fields.*.default' => 'nullable',
            'blocks.*.fields.*.dependencies' => 'nullable|array',
            'blocks.*.fields.*.dependencies.*' => Rule::in($templates),
            'blocks.*.fields.*.options' => 'required_if:blocks.*.fields.*.type,select|array',
            'blocks.*.fields.*.options.*.id' => 'required_with:blocks.*.fields.*.options|numeric',
            'blocks.*.fields.*.options.*.label' => 'required_with:blocks.*.fields.*.options|string',
            'blocks.*.fields.*.options.*.value' => 'required_with:blocks.*.fields.*.options|string',
            'blocks.*.fields.*.options.*.dependencies' => 'nullable|array',
            'blocks.*.fields.*.options.*.dependencies.*' => Rule::in($templates),
        ];

        $validator = Validator::make($jsonData, $rules);

        if ($validator->fails()) {
            $error = $validator->errors()->all();
            $this->fail(new NoRetryException($error[0]));
        }

        foreach ($templates as $key => $template) {
            if (!Storage::exists('tmp/' . $folder . '/templates/' . $template . '.twig')) {
                $this->fail(new NoRetryException('Not found template ' . $template . '.twig'));
            }
        }

        $storagePackagePath = app_path() . '/../storage/app/tmp/' . $folder . '/templates';


        View::addLocation($storagePackagePath);


        $resultLint = Artisan::call('twig:lint', [
            '--format' => 'json'
        ]);


        if ($resultLint == 1) {
            $out = json_decode(Artisan::output(), true);
            foreach ($out as $key => $lint) {
                if ($lint['valid'] == false) {
                    $this->fail(new NoRetryException($lint['message']));
                }
            }
        }
    }

    private function collectTemplates($data)
    {
        $templates = [];

        foreach ($data as $key => $value) {
            // Si la clave es 'template', la añade a la lista de templates
            if ($key === 'template') {
                $templates[] = $value;
            }

            // Si el valor es un array, busca recursivamente en él
            if (is_array($value)) {
                $templates = array_merge($templates, $this->collectTemplates($value));
            }
        }

        return $templates;
    }
}
