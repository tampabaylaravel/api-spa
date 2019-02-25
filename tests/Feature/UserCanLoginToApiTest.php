<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Client;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanLoginToApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function userCanGetAccessTokenAfterLoggingIn()
    {
        $oauthClient = factory(Client::class)->states('password')->create();
        $user = factory(User::class)->create();

        $response = $this->postJson('api/auth/token', [
            'grant_type' => 'password',
            'client_id' => $oauthClient->id,
            'client_secret' => $oauthClient->secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => '*',
        ]);

        $response->assertStatus(200);

        $json = $response->json();
        $this->assertEquals('Bearer', $json['token_type']);
        $this->assertNotEmpty($json['expires_in']);
        $this->assertNotEmpty($json['access_token']);
        $this->assertNotEmpty($json['refresh_token']);
    }
}
