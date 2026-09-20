<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Business;
use Authorization\IdentityInterface;

/**
 * A Business is its own tenant - "ownership" here means the identity's
 * business_id matches the Business's own id, not a foreign key.
 */
class BusinessPolicy
{
    /**
     * Any logged-in User may view their own Business.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Business $business Business.
     * @return bool
     */
    public function canView(IdentityInterface $identity, Business $business): bool
    {
        return (int)$identity['business_id'] === $business->id;
    }

    /**
     * Only the owner may edit business/billing details.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Business $business Business.
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, Business $business): bool
    {
        return (int)$identity['business_id'] === $business->id
            && $identity['role'] === 'owner';
    }

    /**
     * Only the owner may delete their Business.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Business $business Business.
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, Business $business): bool
    {
        return $this->canEdit($identity, $business);
    }

    /**
     * Only the owner may start or manage a subscription - the same rule as
     * editing business/billing details.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Business $business Business.
     * @return bool
     */
    public function canCheckout(IdentityInterface $identity, Business $business): bool
    {
        return $this->canEdit($identity, $business);
    }

    /**
     * Only the owner may open the Stripe Customer Portal - the same rule
     * as starting checkout or editing business/billing details.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Business $business Business.
     * @return bool
     */
    public function canBillingPortal(IdentityInterface $identity, Business $business): bool
    {
        return $this->canEdit($identity, $business);
    }
}
