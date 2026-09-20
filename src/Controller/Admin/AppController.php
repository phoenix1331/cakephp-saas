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
     * Actions reachable once a Business has lost dashboard access
     * (Business::hasAccess() is false) - everything an owner needs to see
     * why they're gated and pay their way out, plus logout. Keyed by
     * controller name so BookingsController::view() (say) isn't
     * accidentally exempted just because BusinessesController::view() is.
     *
     * @var array<string, array<string>>
     */
    private const TRIAL_GATE_EXEMPT_ACTIONS = [
        'Businesses' => ['index', 'view', 'checkout', 'checkoutSuccess', 'billingPortal'],
        'Users' => ['logout'],
    ];

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
     * logged-in identity's business_id, then gates the rest of the
     * dashboard behind Business::hasAccess() (the 14-day trial, or an
     * active/trialing Stripe subscription) - everything except the
     * exemptions above redirects to the Business page instead, where the
     * Subscribe/Manage Billing links already live.
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

        $controllerName = $this->request->getParam('controller');
        $exemptActions = self::TRIAL_GATE_EXEMPT_ACTIONS[$controllerName] ?? [];
        if (in_array($this->request->getParam('action'), $exemptActions, true)) {
            return;
        }

        $business = $this->fetchTable('Businesses')->get($businessId);
        if ($business->hasAccess()) {
            return;
        }

        // The gate fires before the action's own authorize() call, so it
        // must satisfy AuthorizationMiddleware's "every request performs a
        // check" requirement itself.
        $this->Authorization->skipAuthorization();
        $this->Flash->error(__('Your trial has ended. Please subscribe to continue.'));

        $event->setResult($this->redirect(['controller' => 'Businesses', 'action' => 'view', $businessId]));
    }
}
