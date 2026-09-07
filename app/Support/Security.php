<?php

namespace App\Support;

use App\Models\Employee\Employee;
use Illuminate\Contracts\Auth\Authenticatable;

class Security
{
    /**
     * Daftar hierarki role: role => ranking.
     * Makin tinggi ranking, makin luas akses (mensimulasikan security.yaml Symfony).
     */
    public function ranks(): array
    {
        return config('hris.role_ranks', []);
    }

    public function rank(string $role): ?int
    {
        $role = $this->normalizeRole($role);

        return $this->ranks()[$role] ?? null;
    }

    /**
     * Ranking tertinggi yang dimiliki seorang user.
     */
    public function userRank(Authenticatable $user): ?int
    {
        if (! $user instanceof Employee) {
            return null;
        }

        $rank = null;

        foreach ($this->ranks() as $role => $value) {
            if ($user->hasRole($role) && ($rank === null || $value > $rank)) {
                $rank = $value;
            }
        }

        return $rank;
    }

    /**
     * Role minimum yang menaungi sebuah menu (kunci dari config/hris.security).
     */
    public function menuRole(string $menuKey): ?string
    {
        return config("hris.security.{$menuKey}");
    }

    public function menuRank(string $menuKey): ?int
    {
        return $this->rank((string) $this->menuRole($menuKey));
    }

    public function canAccessMenu(Authenticatable $user, string $menuKey): bool
    {
        $rank = $this->userRank($user);

        if ($rank === null) {
            return false;
        }

        $minRank = $this->menuRank($menuKey);

        return $minRank !== null && $rank >= $minRank;
    }

    /**
     * Ranking minimum untuk sebuah ability yang terdaftar di config/hris.abilities.
     */
    public function abilityRank(string $ability): ?int
    {
        $menuKey = config("hris.abilities.{$ability}");

        if ($menuKey === null) {
            return null;
        }

        return $this->menuRank($menuKey);
    }

    public function can(Authenticatable $user, string $ability): bool
    {
        $rank = $this->userRank($user);

        if ($rank === null) {
            return false;
        }

        $minRank = $this->abilityRank($ability);

        return $minRank !== null && $rank >= $minRank;
    }

    /**
     * Beri toleransi awalan "ROLE_" (nama lama Symfony).
     */
    protected function normalizeRole(string $role): string
    {
        return str_starts_with($role, 'ROLE_') ? substr($role, 5) : $role;
    }
}