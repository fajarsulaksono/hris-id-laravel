<?php

namespace App\Http\Middleware;

use App\Support\Security;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRoleRank
{
    public function __construct(protected Security $security)
    {
    }

    /**
     * Parameter bisa berupa kunci menu (mis. `checkRole:master_menu`) atau nama role
     * (mis. `checkRole:HRSTAFF`). User lolos bila ranking tertingginya >= ranking minimal.
     */
    public function handle(Request $request, Closure $next, string $parameter): Response
    {
        $user = Auth::user();

        if ($user === null) {
            abort(403);
        }

        $minRank = $this->security->menuRank($parameter) ?? $this->security->rank($parameter);

        if ($minRank === null || $this->security->userRank($user) < $minRank) {
            abort(403);
        }

        return $next($request);
    }
}