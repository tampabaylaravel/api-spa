<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Laravel\Passport\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanRegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function userCanRegister()
    {
        $oauthClient = factory(Client::class)->states('password')->create();
        $response = $this->postJson('api/register', [
            'name' => 'James',
            'email' => 'fatboyxpc@gmail.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'grant_type' => 'password',
            'client_id' => $oauthClient->id,
            'client_secret' => $oauthClient->secret,
            'scope' => '*',
        ]);

        $response->assertStatus(201);

        $json = $response->json();
        $this->assertEquals('Bearer', $json['token_type']);
        $this->assertNotEmpty($json['expires_in']);
        $this->assertNotEmpty($json['access_token']);
        $this->assertNotEmpty($json['refresh_token']);

        $user = User::first();

        $this->assertEquals('James', $user->name);
        $this->assertEquals('fatboyxpc@gmail.com', $user->email);
        $this->assertTrue(Auth::attempt(['email' => $user->email, 'password' => 'secret']));
    }
}
