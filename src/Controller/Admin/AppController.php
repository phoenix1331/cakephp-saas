<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as BaseAppController;
use Cake\Event\EventInterface;
use Cake\ORM\TableRegistry;

/**
 * Base controller for the owner/staff dashboard. Every action requires a
 * logged-in User, unlike the public BaseAppController which disables the
 * identity check entirely.
 */
class AppController extends BaseAppController
{
    /**
     * Every Table with TenantScopeBehavior attached - kept as an explicit
     * list rather than discovered via associations, since a controller or
     * template can reach a tenant-scoped table through any number of
     * association hops (e.g. Bookings->Customers).
     *
     * @var array<string>
     */
    private const TENANT_SCOPED_TABLES = ['Users', 'Services', 'Customers', 'Bookings'];

    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Authentication->setConfig('requireIdentity', true);
        $this->Authentication->addUnauthenticatedActions(['login', 'signup']);
    }

    /**
     * Sets the current tenant on every tenant-scoped table, using the
     * logged-in identity's business_id. Runs once the identity is resolved,
     * so login and signup (which have no identity yet) are unaffected.
     *
     * @param \Cake\Event\EventInterface $event The beforeFilter event.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $identity = $this->Authentication->getIdentity();
        if ($identity === null) {
            return;
        }

        $businessId = (int)$identity['business_id'];
        foreach (self::TENANT_SCOPED_TABLES as $alias) {
            TableRegistry::getTableLocator()->get($alias)->behaviors()
                ->get('TenantScope')
                ->setTenantId($businessId);
        }
    }
}
