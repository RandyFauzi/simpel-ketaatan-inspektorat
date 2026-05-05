<?php

namespace App\Policies;

use App\Models\Lhp;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LhpPolicy
{
    use HandlesAuthorization;

    private function isKetuaLike(?string $role): bool
    {
        return in_array((string) $role, ['ketua_tim', 'skpd', 'pengendali_teknis'], true);
    }

    public function update(User $user, Lhp $lhp): bool
    {
        if (in_array($user->role, ['admin', 'auditor'], true)) {
            return true;
        }

        return $this->isKetuaLike($user->role) && (string) $user->tim !== '' && $user->tim === $lhp->tim;
    }

    public function review(User $user, Lhp $lhp): bool
    {
        return $this->isKetuaLike($user->role) && $lhp->status === 'review_ketua' && $user->tim === $lhp->tim;
    }
}
