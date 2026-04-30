<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\DashboardStatsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DashboardStatsController extends Controller
{
    public function __construct(
        private readonly DashboardStatsService $dashboardStatsService,
    ) {
    }

    public function show(Request $request): JsonResponse
    {
        $userId = (int) ($request->input('user_id') ?: 0);
        $user = $userId > 0
            ? User::query()->findOrFail($userId)
            : User::query()->firstOrCreate(
                ['email' => 'dev.study@example.com'],
                ['name' => 'Dev Study User', 'password' => Hash::make('password')]
            );

        return response()->json($this->dashboardStatsService->build($user));
    }
}
