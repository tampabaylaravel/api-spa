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

    private function validationTest($data = [])
    {
        $response = $this->postJson($this->route, $this->validParams($data));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(array_keys($data));
    }

    /** @test */
    public function nameMustBeString()
    {
        $this->validationTest(['name' => 123]);
    }

    /** @test */
    public function nameMustNotBeGreaterThan255Characters()
    {
       $this->validationTest(['name' => str_random(256)]);
    }

    /** @test */
    public function emailMustBeString()
    {
        $this->validationTest(['email' => 123]);
    }

    /** @test */
    public function emailMustBeValidEmailAddress()
    {
        $this->validationTest(['email' => 'fatboyxpc']);
    }

    /** @test */
    public function emailMustNotBeGreaterThan255Characters()
    {
        $this->validationTest(['email' => str_random(256)]);
    }

    /** @test */
    public function emailMustBeUnique()
    {
        $user = factory(User::class)->create([
            'email' => 'fatboyxpc@gmail.com',
        ]);

        $this->validationTest(['email' => 'fatboyxpc@gmail.com']);
    }

    /** @test */
    public function passwordMustBeString()
    {
        $this->validationTest(['password' => 123456]);
    }

    /** @test */
    public function passwordMustNotBeLessThan6Characters()
    {
        $this->validationTest(['password' => str_random(5)]);
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
