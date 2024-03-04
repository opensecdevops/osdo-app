<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;

use Tests\TestCase;
use Inertia\Testing\AssertableInertia as Assert;
use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Crypt;

class TokensServicesTest extends TestCase
{

    use RefreshDatabase;
    //use DatabaseMigrations;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();


        $this->user = User::factory()->create([
            'name' => 'John',
            'email' => 'johndoe@example.com',
        ]);
    }



    public function test_get_page_add_service(): void
    {
        $this->actingAs($this->user)
            ->get(route('service.edit'))
            ->assertInertia(
                fn (Assert $assert) => $assert
                    ->component('Profile/Services')
                    ->has('servicesUser.data', 0)
                    ->has('services', 1)
                    ->has(
                        'services.0',
                        fn (Assert $assert) => $assert
                            ->where('id', 1)
                            ->where('service', 'gitlab')
                    )
                    ->has('errors')
                    ->where('errors', [])
            );
    }

    public function test_error_register_service(): void
    {
        $this->actingAs($this->user)
            ->get(route('service.edit'));  

        $this
            ->followingRedirects()
            ->post(route('service.store'))
            ->assertInertia(
                fn (Assert $assert) => $assert
                    ->component('Profile/Services')
                    ->has('servicesUser.data', 0)
                    ->has('services', 1)
                    ->has(
                        'services.0',
                        fn (Assert $assert) => $assert
                            ->where('id', 1)
                            ->where('service', 'gitlab')
                    )
                    ->where('errors', [
                        'token' => 'The token field is required.',
                        'service' => 'The service field is required.'
                    ])
            );
    }

    public function test_register_service(): void
    {
        $this->actingAs($this->user)
            ->get(route('service.edit'));  

        $this
            ->followingRedirects()
            ->post(route('service.store'), [
                'token' => '123456789',
                'service' => 1
            ])
            ->assertInertia(
                fn (Assert $assert) => $assert
                    ->component('Profile/Services')
                    ->has('servicesUser.data', 1)
                    ->has('services', 1)
                    ->has(
                        'services.0',
                        fn (Assert $assert) => $assert
                            ->where('id', 1)
                            ->where('service', 'gitlab')
                    )
                    ->where('errors', [])
            );

            

            $db = DB::table('service_user')->where('user_id', $this->user->id)->where('service_id', 1)->first();

            $token =  Crypt::decryptString($db->token);

            $this->assertEquals('123456789', $token);


    }
}
