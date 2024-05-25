<?php

namespace App\Jobs;

use App\Exceptions\NoRetryException;
use App\Models\Package;
use App\Models\PackageVersion;
use App\Models\User;
use App\Traits\ValidationTrait;
use GrahamCampbell\GitLab\Facades\GitLab;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Throwable;
use ZipArchive;

class RetrievePackage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, ValidationTrait;

    protected $package;

    private $storagePath;

    /**
     * Create a new job instance.
     */
    public function __construct(Package $package)
    {
        $this->package = $package;
        $this->storagePath = Storage::path('/');

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

        //get service and user of package
        $user = User::find($this->package->user_id);
        $service_id = $this->package->service_id;
        $service = $user->services()->where('service_id', $service_id)->first();
        $token = $service->pivot->token;
        $token = Crypt::decryptString($token);

        if ($service_id == 1) {
            config(['gitlab.connections.main.token' => $token]);
            $tags = Gitlab::tags()->all($this->package->repository_id, ['sort' => 'desc', 'order_by' => 'updated']);

            if (empty($tags)) {
                throw new NoRetryException('Not found tags');
            }

            $shaCommit = $tags[0]['commit']['id'];
            $shaSortCommit = $tags[0]['commit']['short_id'];

            $packageVersion = PackageVersion::where('package_id', $this->package->id)->where('commit', $shaSortCommit)->first();

            if ($packageVersion) {
                return;
            }

            $files = GitLab::repositories()->archive($this->package->repository_id, ['sha' => $shaCommit], 'zip');
            Storage::put('tmp/'.$this->package->id.'.zip', $files);
            $zipPath = Storage::path('tmp/'.$this->package->id.'.zip');

            if ($zip->open($zipPath) !== true) {
                new NoRetryException('Not open zip');
            }

            $folderZip = $zip->getNameIndex(0);
            $zip->extractTo($this->storagePath.'/tmp/');
            $zip->close();
            [$isValid, $errorMessage] = $this->validatePackage($folderZip);
            if (! $isValid) {
                throw new NoRetryException($errorMessage);
            }

            $folderPackage = sprintf('packages/%s/%s/%s', $service->service, $this->package->name, $shaSortCommit);
            if (! Storage::exists($folderPackage)) {
                Storage::makeDirectory($folderPackage);
            }

            $tmpFolderPackage = sprintf('tmp/%s', $folderZip);

            if (Storage::move($tmpFolderPackage, $folderPackage)) {
                Storage::deleteDirectory($tmpFolderPackage);
            }

            $config = Storage::get($folderPackage.'/config.json');

            $jsonData = json_decode($config, true);

            $packageVersion = new PackageVersion();
            $packageVersion->package_id = $this->package->id;
            $packageVersion->version = $jsonData['version'];
            $packageVersion->commit = $shaSortCommit;
            $packageVersion->description = $tags[0]['message'];
            $packageVersion->save();

            $this->package->message = 'success';
            $this->package->status = 1;
            $this->package->save();

        }
    }

    public function failed(Throwable $exception)
    {
        if ($exception instanceof NoRetryException) {
            $this->package->message = $exception->getMessage();
            $this->package->status = 2;
            $this->package->save();
            $this->delete();
        }
    }
}
