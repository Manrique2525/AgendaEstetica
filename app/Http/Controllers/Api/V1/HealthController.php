<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;

final class HealthController
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => [
                'status' => 'ok',
            ],
        ]);
    }
}
