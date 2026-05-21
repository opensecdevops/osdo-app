<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Package;
use App\Models\User;
use Tests\TestCase;
use GrahamCampbell\GitLab\Facades\GitLab;
use App\Jobs\RetrievePackage;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;


class RetrievePackageTest extends TestCase
{
    use RefreshDatabase;
    //use DatabaseMigrations;

    protected $user;

    private $tags = [
        0 =>  [
            "name" => "0.0.1",
            "message" => "First version",
            "target" => "6b07be6889afb89bdd831e5a7cbc0c7fc698918f",
            "commit" => [
                "id" => "ee0413d2dd9c31b92e003e4c823067901eb59941",
                "short_id" => "ee0413d2",
                "created_at" => "2023-11-27T16:40:34.000+00:00",
                "parent_ids" => [
                    0 => "9ff58ab7fc44c62e6d50dda09826f08b055545b0"
                ],
                "title" => "add english",
                "message" => "add english",
                "author_name" => "GoldraK",
                "author_email" => "1412316-Goldrak@users.noreply.gitlab.com",
                "authored_date" => "2023-11-27T16:40:34.000+00:00",
                "committer_name" => "GoldraK",
                "committer_email" => "1412316-Goldrak@users.noreply.gitlab.com",
                "committed_date" => "2023-11-27T16:40:34.000+00:00",
                "trailers" => [],
                "web_url" => "https://gitlab.com/opensecdevops/pipelines/laravel-inertiajs-ci-for-gitlab/-/commit/ee0413d2dd9c31b92e003e4c823067901eb59941",
            ],
            "release" => null,
            "protected" => false,
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();


        $this->user = User::factory()->create([
            'name' => 'John',
            'email' => 'johndoe@example.com',
        ]);
    }


    public function testJobHandlesNoTagsError()
    {

        $this->user->services()->attach(1, ['token' => Crypt::encryptString('1234567890')]);

        $package = Package::factory()->create(['user_id' => $this->user->id, 'service_id' => 1]);

        GitLab::shouldReceive('tags->all')
            ->andReturn([]);

        $job = new RetrievePackage($package);

        $this->expectException(\App\Exceptions\NoRetryException::class);

        $job->handle();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => 2,
            'message' => 'No tags found',
        ]);
    }

    public function testJobValidateImport()
    {
        Storage::fake('local');

        $this->user->services()->attach(1, ['token' => Crypt::encryptString('1234567890')]);
        // Configura tus mocks y fakes aquí
        $package = Package::factory()->create(['user_id' => $this->user->id, 'service_id' => 1]);

        $zipContent = file_get_contents(base_path('/tests/Fixtures/zips/full_package.zip'));

        GitLab::shouldReceive('tags->all')
            ->andReturn($this->tags);

        GitLab::shouldReceive('repositories->archive')
            ->withArgs([$package->repository_id, ['sha' => $this->tags[0]['commit']['id']], 'zip'])
            ->andReturn($zipContent);

        $job = new RetrievePackage($package);

        $job->handle();

        Storage::disk('local')->assertExists('tmp/' . $package->id . '.zip');
        $folderPackage = sprintf('packages/%s/%s/%s', 'gitlab', $package->name, $this->tags[0]['commit']['short_id']);

        Storage::disk('local')->assertExists($folderPackage . '/config.json');
        Storage::disk('local')->assertExists($folderPackage . '/README.md');
        $config = Storage::disk('local')->get($folderPackage . '/config.json');
        $config = json_decode($config, true);
        Storage::disk('local')->assertExists($folderPackage . '/templates/' . $config['template'] . '.twig');
        foreach ($config['blocks'] as $block) {
            Storage::disk('local')->assertExists($folderPackage . '/templates/' . $block['template'] . '.twig');
        }

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => 1,
            'message' => 'success',
        ]);

        $this->assertDatabaseHas('package_versions', [
            'package_id' => $package->id,
            'version' => $config['version'],
            'commit' => $this->tags[0]['commit']['short_id'],
            'description' => $this->tags[0]['message'],
        ]);
    }

    public function testJobValidateNoTiwgFound()
    {
        Storage::fake('local');

        $this->user->services()->attach(1, ['token' => Crypt::encryptString('1234567890')]);

        $package = Package::factory()->create(['user_id' => $this->user->id, 'service_id' => 1]);

        $zipContent = file_get_contents(base_path('/tests/Fixtures/zips/test_twig_not_found.zip'));

        GitLab::shouldReceive('tags->all')
            ->andReturn($this->tags);

        GitLab::shouldReceive('repositories->archive')
            ->withArgs([$package->repository_id, ['sha' => $this->tags[0]['commit']['id']], 'zip'])
            ->andReturn($zipContent);

        $job = new RetrievePackage($package);

        $this->expectException(\App\Exceptions\NoRetryException::class);
        $this->expectExceptionMessage('Not found template defectdojo.twig');

        $job->handle();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => 2,
            'message' => 'Not found template defectdojo.twig',
        ]);
    }

    /*public function testJobValidateNoTiwgValid()
    {
        Storage::fake('local');

        $this->user->services()->attach(1, ['token' => Crypt::encryptString('1234567890')]);
        // Configura tus mocks y fakes aquí
        $package = Package::factory()->create(['user_id' => $this->user->id, 'service_id' => 1]);

        $zipContent = file_get_contents(base_path('/tests/Fixtures/zips/test_twig_not_valid.zip'));

        GitLab::shouldReceive('tags->all')
            ->andReturn($this->tags);

        GitLab::shouldReceive('repositories->archive')
            ->withArgs([$package->repository_id, ['sha' => $this->tags[0]['commit']['id']], 'zip'])
            ->andReturn($zipContent);

        $job = new RetrievePackage($package);
        // Espera una excepción
        $this->expectException(\App\Exceptions\NoRetryException::class);
        $this->expectExceptionMessage('Not found template defectdojo.twig');

        $job->handle();

        $this->assertDatabaseHas('packages', [
            'id' => $package->id,
            'status' => 2,
            'message' => 'Not found template defectdojo.twig',
        ]);
    }*/
}
