<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

it('creates an administrator with normalized email and hashed password', function (): void {
    $this->artisan('admin:create', [
        '--name' => 'Foundation Command Admin',
        '--email' => 'ADMIN-COMMAND@EXAMPLE.INVALID',
    ])
        ->expectsQuestion('Password (minimum 12 characters)', 'a-strong-local-password')
        ->expectsQuestion('Confirm password', 'a-strong-local-password')
        ->assertSuccessful();

    $user = User::query()->where('email', 'admin-command@example.invalid')->firstOrFail();

    expect($user->name)->toBe('Foundation Command Admin')
        ->and(Hash::check('a-strong-local-password', $user->password))->toBeTrue();
});

it('rejects duplicate administrator emails', function (): void {
    User::factory()->create(['email' => 'duplicate@example.invalid']);

    $this->artisan('admin:create', [
        '--name' => 'Duplicate Admin',
        '--email' => 'DUPLICATE@EXAMPLE.INVALID',
    ])->assertFailed();

    expect(User::query()->where('email', 'duplicate@example.invalid')->count())->toBe(1);
});
