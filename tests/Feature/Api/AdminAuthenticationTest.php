<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('rejects an unauthenticated current-user request', function (): void {
    $this->getJson('/api/v1/admin/auth/me')
        ->assertUnauthorized()
        ->assertJsonStructure(['message']);
});

it('validates login input without user enumeration', function (): void {
    $this->postJson('/api/v1/admin/auth/login', [])
        ->assertStatus(422)
        ->assertJsonStructure(['message', 'errors' => ['email', 'password']]);

    $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'invalid-email',
        'password' => 'password',
    ])->assertStatus(422);
});

it('rejects invalid credentials with a generic response', function (): void {
    User::factory()->create([
        'email' => 'admin@example.invalid',
        'password' => 'correct-password',
    ]);

    $this->postJson('/api/v1/admin/auth/login', [
        'email' => 'admin@example.invalid',
        'password' => 'wrong-password',
    ])->assertStatus(422)
        ->assertJsonPath('message', 'Las credenciales proporcionadas no son correctas.')
        ->assertJsonPath('errors.email.0', 'Las credenciales proporcionadas no son correctas.');
});

it('authenticates, returns the current user, and logs out', function (): void {
    $password = 'correct-password';
    User::factory()->create([
        'name' => 'Foundation Admin',
        'email' => 'foundation-admin@example.invalid',
        'password' => $password,
    ]);

    $this->withHeaders(['Origin' => 'http://127.0.0.1:8000'])
        ->postJson('/api/v1/admin/auth/login', [
            'email' => 'foundation-admin@example.invalid',
            'password' => $password,
        ])->assertOk()
        ->assertJsonPath('data.name', 'Foundation Admin')
        ->assertJsonPath('data.email', 'foundation-admin@example.invalid')
        ->assertJsonMissingPath('data.password');

    $this->withHeaders(['Origin' => 'http://127.0.0.1:8000'])
        ->getJson('/api/v1/admin/auth/me')
        ->assertOk()
        ->assertJsonPath('data.email', 'foundation-admin@example.invalid');

    $this->withHeaders(['Origin' => 'http://127.0.0.1:8000'])
        ->postJson('/api/v1/admin/auth/logout')
        ->assertNoContent();

    app('auth')->forgetGuards();

    $this->withHeaders(['Origin' => 'http://127.0.0.1:8000'])
        ->getJson('/api/v1/admin/auth/me')
        ->assertUnauthorized();
});

it('limits repeated invalid login attempts', function (): void {
    $payload = [
        'email' => 'unknown-rate-limit@example.invalid',
        'password' => 'wrong-password',
    ];

    foreach (range(1, 5) as $attempt) {
        $this->postJson('/api/v1/admin/auth/login', $payload)->assertStatus(422);
    }

    $this->postJson('/api/v1/admin/auth/login', $payload)->assertTooManyRequests();
});

it('does not expose password hashes through the user model response', function (): void {
    $user = User::factory()->create([
        'email' => 'hidden-fields@example.invalid',
        'password' => 'correct-password',
    ]);

    expect(Hash::check('correct-password', $user->password))->toBeTrue();
    expect($user->toArray())->not->toHaveKey('password');
    expect($user->toArray())->not->toHaveKey('remember_token');
});
