<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\User;
use Authorization\IdentityInterface;

class UserPolicy
{
    /**
     * Any User may view staff within their own Business.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\User $user User.
     * @return bool
     */
    public function canView(IdentityInterface $identity, User $user): bool
    {
        return (int)$identity['business_id'] === $user->business_id;
    }

    /**
     * Any User may add staff to their own Business - the brief restricts
     * only billing edits and staff deletion to the owner role, not invites.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\User $user User.
     * @return bool
     */
    public function canAdd(IdentityInterface $identity, User $user): bool
    {
        return (int)$identity['business_id'] === (int)$user->business_id;
    }

    /**
     * A User may edit their own record; only the owner may edit someone
     * else's within the same Business.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\User $user User.
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, User $user): bool
    {
        if ((int)$identity['business_id'] !== $user->business_id) {
            return false;
        }

        return $identity['role'] === 'owner' || (int)$identity->getIdentifier() === $user->id;
    }

    /**
     * Only the owner may delete staff - explicit in the brief.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\User $user User.
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, User $user): bool
    {
        return (int)$identity['business_id'] === $user->business_id
            && $identity['role'] === 'owner';
    }
}
