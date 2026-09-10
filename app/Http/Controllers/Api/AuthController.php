<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\AuthUserResource;
use App\Models\Employee\Employee;
use App\Support\Security;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function __construct(protected Security $security) {}

    /**
     * Tukar username + password dengan Sanctum token.
     * Ability token dihitung dari ranking role tertinggi pemakai.
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
            'device_name' => ['nullable', 'string'],
        ]);

        $deviceName = $credentials['device_name'] ?? $request->input('device_name') ?? 'api-token';

        $employee = Employee::where('username', $credentials['username'])->first();

        if (! $employee || ! Hash::check($credentials['password'], $employee->password)) {
            throw ValidationException::withMessages([
                'username' => ['Username atau password salah.'],
            ]);
        }

        $token = $employee->createToken($deviceName, $this->abilitiesFor($employee));

        return response()->json([
            'token' => $token->plainTextToken,
            'user' => new AuthUserResource($employee),
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()?->currentAccessToken()?->delete();

        return response()->json(['message' => 'Logged out.']);
    }

    public function me(Request $request): JsonResponse
    {
        return (new AuthUserResource($request->user()))->response();
    }

    /**
     * Ability yang layak dimiliki token sesuai kemampuan menu pemakai.
     */
    protected function abilitiesFor(Employee $employee): array
    {
        $abilities = [];

        foreach (array_keys(config('hris.abilities', [])) as $ability) {
            if ($this->security->can($employee, $ability)) {
                $abilities[] = $ability;
            }
        }

        return $abilities;
    }
}
