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

    protected function setUp()
    {
        parent::setUp();

        $this->oauthClient = factory(Client::class)->states('password')->create();
        $this->route = 'api/register';
    }

    /** @test */
    public function userCanRegister()
    {
        $response = $this->postJson($this->route, [
            'name' => 'James',
            'email' => 'fatboyxpc@gmail.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
            'grant_type' => 'password',
            'client_id' => $this->oauthClient->id,
            'client_secret' => $this->oauthClient->secret,
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

    /** @test */
    public function theseFieldsAreRequiredToRegister()
    {
        $response = $this->postJson($this->route);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'name',
            'email',
            'password',
        ]);
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'name' => 'James',
            'email' => 'fatboyxpc@gmail.com',
            'password' => 'secret',
            'password_confirmation' => 'secret',
        ], $overrides);
    }

    /** @test */
    public function nameMustBeString()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'name' => 123,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function nameMustNotBeGreaterThan255Characters()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'name' => str_random(256),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function emailMustBeString()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'email' => 123,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailMustBeValidEmailAddress()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'email' => 'fatboyxpc',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailMustNotBeGreaterThan255Characters()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'email' => str_random(256),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailMustBeUnique()
    {
        $user = factory(User::class)->create([
            'email' => 'fatboyxpc@gmail.com',
        ]);

        $response = $this->postJson($this->route, $this->validParams([
            'email' => 'fatboyxpc@gmail.com',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function passwordMustBeString()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'password' => 123456,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    /** @test */
    public function passwordMustNotBeLessThan6Characters()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'password' => str_random(5),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    /** @test */
    public function passwordMustMatchConfirmation()
    {
        $response = $this->postJson($this->route, $this->validParams([
            'password' => 'secret',
            'password_confirmation' => 'secret2',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    private function validOauthParams($overrides = [])
    {
        return array_merge($this->validParams([
            'grant_type' => 'password',
            'client_id' => $this->oauthClient->id,
            'client_secret' => $this->oauthClient->secret,
        ]), $overrides);
    }

    /** @test */
    public function userCannotRegisterWithoutValidGrantType()
    {
        $response = $this->postJson($this->route, $this->validOauthParams([
            'grant_type' => '',
        ]));

        $response->assertStatus(400);

        $json = $response->json();
        $this->assertEquals('unsupported_grant_type', $json['error']);
    }

    /** @test */
    public function userCannotRegisterWithoutValidClientId()
    {
        $response = $this->postJson($this->route, $this->validOauthParams([
            'client_id' => '',
        ]));

        $response->assertStatus(400);

        $json = $response->json();
        $this->assertEquals('invalid_request', $json['error']);
        $this->assertContains('client_id', $json['hint']);
    }

    /** @test */
    public function userCannotRegisterWithoutValidClientSecret()
    {
        $response = $this->postJson($this->route, $this->validOauthParams([
            'client_secret' => '',
        ]));

        $response->assertStatus(401);

        $json = $response->json();
        $this->assertEquals('invalid_client', $json['error']);
    }
}
