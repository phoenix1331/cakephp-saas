<?php
declare(strict_types=1);

namespace TenantScope;

use Cake\Core\BasePlugin;

/**
 * Single-database, shared-schema multi-tenancy as a reusable behavior -
 * see TenantScopeBehavior. No routes, middleware or console commands of
 * its own, so most of BasePlugin's hooks are left at their defaults.
 */
class TenantScopePlugin extends BasePlugin
{
    protected bool $routesEnabled = false;
    protected bool $consoleEnabled = false;
    protected bool $middlewareEnabled = false;
}
