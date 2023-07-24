<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;


    public function test_login_with_valid_credentials_returns_token(): void
    {
        $user = User::factory()->create();

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);
        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token']);
    }


    public function test_login_with_invalid_credentials_returns_error(): void
    {
        $response = $this->postJson('api/v1/login', [
            'email' => 'invalid@user.com',
            'password' => 'password',
        ]);
        $response->assertStatus(422);
    }
}
