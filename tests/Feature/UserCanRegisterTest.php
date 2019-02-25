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

    /** @test */
    public function theseFieldsAreRequiredToRegister()
    {
        $response = $this->postJson('api/register');

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
        $response = $this->postJson('api/register', $this->validParams([
            'name' => 123,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function nameMustNotBeGreaterThan255Characters()
    {
        $response = $this->postJson('api/register', $this->validParams([
            'name' => str_random(256),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('name');
    }

    /** @test */
    public function emailMustBeString()
    {
        $response = $this->postJson('api/register', $this->validParams([
            'email' => 123,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailMustBeValidEmailAddress()
    {
        $response = $this->postJson('api/register', $this->validParams([
            'email' => 'fatboyxpc',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailMustNotBeGreaterThan255Characters()
    {
        $response = $this->postJson('api/register', $this->validParams([
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

        $response = $this->postJson('api/register', $this->validParams([
            'email' => 'fatboyxpc@gmail.com',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    /** @test */
    public function passwordMustBeString()
    {
        $response = $this->postJson('api/register', $this->validParams([
            'password' => 123456,
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    /** @test */
    public function passwordMustNotBeLessThan6Characters()
    {
        $response = $this->postJson('api/register', $this->validParams([
            'password' => str_random(5),
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }

    /** @test */
    public function passwordMustMatchConfirmation()
    {
        $response = $this->postJson('api/register', $this->validParams([
            'password' => 'secret',
            'password_confirmation' => 'secret2',
        ]));

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('password');
    }
}
