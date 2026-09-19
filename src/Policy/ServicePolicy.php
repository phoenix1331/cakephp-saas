<?php
declare(strict_types=1);

namespace App\Policy;

use App\Model\Entity\Service;
use Authorization\IdentityInterface;

class ServicePolicy
{
    /**
     * Any User may view/manage Services within their own Business - the
     * brief doesn't restrict Service management to the owner role.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Service $service Service.
     * @return bool
     */
    public function canView(IdentityInterface $identity, Service $service): bool
    {
        return (int)$identity['business_id'] === $service->business_id;
    }

    /**
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Service $service Service.
     * @return bool
     */
    public function canEdit(IdentityInterface $identity, Service $service): bool
    {
        return $this->canView($identity, $service);
    }

    /**
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Service $service Service.
     * @return bool
     */
    public function canDelete(IdentityInterface $identity, Service $service): bool
    {
        return $this->canView($identity, $service);
    }

    /**
     * Any logged-in User may add a Service to their own Business - checked
     * against a new, unsaved entity, so business_id is set from the request
     * and must be verified rather than trusted.
     *
     * @param \Authorization\IdentityInterface $identity Identity.
     * @param \App\Model\Entity\Service $service Service.
     * @return bool
     */
    public function canAdd(IdentityInterface $identity, Service $service): bool
    {
        return (int)$identity['business_id'] === (int)$service->business_id;
    }
}
