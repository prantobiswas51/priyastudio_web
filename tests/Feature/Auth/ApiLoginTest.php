<?php

use App\Models\User;

test('user can login through api and receive token', function () {
    $user = User::factory()->create([
        'email' => 'api-user@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => 'api-user@example.com',
        'password' => 'password',
        'device_name' => 'android-app',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'message',
            'token_type',
            'access_token',
            'user' => ['id', 'name', 'email'],
        ]);
});

test('api login sanitizes email and rejects invalid credentials', function () {
    User::factory()->create([
        'email' => 'sanitize@example.com',
        'password' => 'password',
    ]);

    $response = $this->postJson('/api/login', [
        'email' => '  SANITIZE@example.com  ',
        'password' => "wrong-password\n",
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => trans('auth.failed'),
        ]);
});
