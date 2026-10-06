<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\AuthenticateAdminAction;
use App\Http\Requests\AdminLoginRequest;
use App\Http\Resources\AuthenticatedUserResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

final class AdminAuthController
{
    public function login(
        AdminLoginRequest $request,
        AuthenticateAdminAction $authenticateAdmin,
    ): AuthenticatedUserResource {
        return new AuthenticatedUserResource($authenticateAdmin->execute($request));
    }

    public function me(Request $request): AuthenticatedUserResource
    {
        return new AuthenticatedUserResource($request->user('sanctum'));
    }

    public function logout(Request $request): Response
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
