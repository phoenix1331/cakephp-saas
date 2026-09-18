<?php
declare(strict_types=1);

namespace App\Controller\Admin;

use App\Controller\AppController as BaseAppController;

/**
 * Base controller for the owner/staff dashboard. Every action requires a
 * logged-in User, unlike the public BaseAppController which disables the
 * identity check entirely.
 */
class AppController extends BaseAppController
{
    /**
     * Initialization hook method.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->Authentication->setConfig('requireIdentity', true);
        $this->Authentication->addUnauthenticatedActions(['login']);
    }
}
