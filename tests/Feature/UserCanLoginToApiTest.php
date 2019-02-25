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

    private $oauthClient;

    private $user;

    protected function setUp()
    {
        parent::setUp();

        $this->oauthClient = factory(Client::class)->states('password')->create();
        $this->user = factory(User::class)->create();
    }

    /** @test */
    public function userCanGetAccessTokenAfterLoggingIn()
    {
        $response = $this->postJson('api/auth/token', [
            'grant_type' => 'password',
            'client_id' => $this->oauthClient->id,
            'client_secret' => $this->oauthClient->secret,
            'username' => $this->user->email,
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

    private function validOauthParams($overrides = [])
    {
        return array_merge([
            'grant_type' => 'password',
            'client_id' => $this->oauthClient->id,
            'client_secret' => $this->oauthClient->secret,
            'username' => $this->user->email,
            'password' => 'secret',
        ], $overrides);
    }

    /** @test */
    public function userCannotLoginWithoutValidGrantType()
    {
        $response = $this->postJson('api/auth/token', $this->validOauthParams([
            'grant_type' => '',
        ]));

        $response->assertStatus(400);

        $json = $response->json();
        $this->assertEquals('unsupported_grant_type', $json['error']);
    }

    /** @test */
    public function userCannotLoginWithoutValidClientId()
    {
        $response = $this->postJson('api/auth/token', $this->validOauthParams([
            'client_id' => '',
        ]));

        $response->assertStatus(400);

        $json = $response->json();
        $this->assertEquals('invalid_request', $json['error']);
        $this->assertContains('client_id', $json['hint']);
    }

    /** @test */
    public function userCannotLoginWithoutValidClientSecret()
    {
        $response = $this->postJson('api/auth/token', $this->validOauthParams([
            'client_secret' => '',
        ]));

        $response->assertStatus(401);

        $json = $response->json();
        $this->assertEquals('invalid_client', $json['error']);
    }

    /** @test */
    public function userCannotLoginWithoutValidUser()
    {
        $response = $this->postJson('api/auth/token', $this->validOauthParams([
            'username' => '',
        ]));

        $response->assertStatus(400);

        $json = $response->json();
        $this->assertEquals('invalid_request', $json['error']);
        $this->assertContains('username', $json['hint']);
    }

    /** @test */
    public function userCannotLoginWithoutPassword()
    {
        $response = $this->postJson('api/auth/token', $this->validOauthParams([
            'password' => '',
        ]));

        $response->assertStatus(400);

        $json = $response->json();
        $this->assertEquals('invalid_request', $json['error']);
        $this->assertContains('password', $json['hint']);
    }
}
