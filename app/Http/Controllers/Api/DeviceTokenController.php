<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DeviceToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:2048'],
            'platform' => ['required', 'string', 'in:android,ios'],
        ]);

        $deviceToken = DeviceToken::query()->updateOrCreate(
            ['employee_id' => $request->user()->getKey(), 'token' => $data['token']],
            ['platform' => $data['platform']],
        );

        return response()->json(['data' => $deviceToken], 201);
    }

    public function destroy(Request $request, string $id): JsonResponse
    {
        DeviceToken::query()
            ->where('employee_id', $request->user()->getKey())
            ->whereKey($id)
            ->firstOrFail()
            ->delete();

        return response()->json(['message' => 'Device token removed.']);
    }
}
