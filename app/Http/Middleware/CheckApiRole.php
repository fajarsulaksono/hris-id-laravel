<?php

namespace App\Http\Middleware;

use App\Support\Security;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Versi JSON-friendly dari CheckRoleRank untuk API.
 * Parameter sama: kunci menu (mis. `apiRole:view_payroll`) atau nama role
 * (mis. `apiRole:HRSUPERVISOR`). Mengembalikan 403 JSON bila ditolak.
 */
class CheckApiRole
{
    public function __construct(protected Security $security) {}

    public function handle(Request $request, Closure $next, string $parameter): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        $minAbility = $this->security->abilityRank($parameter) ?? $this->security->menuRank($parameter);
        $minRank = $minAbility ?? $this->security->rank($parameter);

        if ($minRank === null || $this->security->userRank($user) < $minRank) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        return $next($request);
    }
}
