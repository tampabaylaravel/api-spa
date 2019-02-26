<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuestCanRecoverPasswordTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function guestCanSendForgotPasswordEmail()
    {
        $user = factory(User::class)->create([
            'email' => 'fatboyxpc@gmail.com',
        ]);

        Notification::fake();

        $response = $this->postJson('api/password/email', [
            'email' => 'fatboyxpc@gmail.com',
        ]);

        $response->assertStatus(200);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    /** @test */
    public function guestCanResetPassword()
    {
        $user = factory(User::class)->create([
            'email' => 'fatboyxpc@gmail.com',
        ]);

        $token = Password::broker()->createToken($user);

        $response = $this->postJson("api/password/reset", [
            'token' => $token,
            'email' => 'fatboyxpc@gmail.com',
            'password' => 'secret2',
            'password_confirmation' => 'secret2',
        ]);

        $response->assertStatus(200);
        $this->assertTrue(Auth::attempt(['email' => 'fatboyxpc@gmail.com', 'password' => 'secret2']));
    }
}
