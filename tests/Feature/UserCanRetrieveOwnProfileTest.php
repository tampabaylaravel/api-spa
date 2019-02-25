<?php

namespace Tests\Feature;

use App\User;
use Laravel\Passport\Passport;
use Tests\TestCase;
use Laravel\Passport\Client;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserCanRetrieveOwnProfileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guestCannotAccessOwnProfileRoute()
    {
        $response = $this->getJson('api/user');

        $response->assertStatus(401);
    }

    /** @test */
    public function userCanGetTheirOwnProfileViaHeader()
    {
        $oauthClient = factory(Client::class)->states('password')->create();
        $user = factory(User::class)->create();

        $tokenResponse = $this->postJson('api/auth/token', [
            'grant_type' => 'password',
            'client_id' => $oauthClient->id,
            'client_secret' => $oauthClient->secret,
            'username' => $user->email,
            'password' => 'secret',
            'scope' => '*',
        ]);

        $response = $this->getJson('api/user', [
            'Authorization' => 'Bearer ' . $tokenResponse->json()['access_token'],
        ]);

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $user->id,
        ]);
    }

    /** @test */
    public function userCanGetTheirOwnProfileViaPassport()
    {
        $user = factory(User::class)->create();
        $user->wasRecentlyCreated = false; // #25868

        Passport::actingAs($user);
        $response = $this->getJson('api/user');

        $response->assertStatus(200);

        $response->assertJsonFragment([
            'id' => $user->id,
        ]);
    }
}
