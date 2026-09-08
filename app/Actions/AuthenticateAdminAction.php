<?php

namespace App\Actions;

use App\Http\Requests\AdminLoginRequest;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

final class AuthenticateAdminAction
{
    public function execute(AdminLoginRequest $request): User
    {
        $credentials = $request->validated();

        if (! Auth::guard('web')->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales proporcionadas no son correctas.'],
            ]);
        }

        $request->session()->regenerate();

        /** @var User $user */
        $user = $request->user('web');

        return $user;
    }
}
