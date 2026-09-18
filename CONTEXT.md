# CONTEXT

## What this is

A booking and scheduling SaaS for solo service businesses (hairdressers, tutors, therapists), built in CakePHP 5 as a deliberate framework-learning exercise mapped against Laravel. Public guest booking flow, owner/staff dashboard, email reminders, and Stripe billing, backed by a shared-schema multi-tenancy model. See `.claude/tasks/BRIEF.md` for the full product brief and Laravel-to-CakePHP concept mapping.

## Architecture at a glance

| Layer | Choice |
|---|---|
| Framework | CakePHP 5.4 |
| Runtime | FrankenPHP (`dunglas/frankenphp:php8.4`), served via `bin/cake server` |
| Database | MySQL 8.4, host port 3307 (3306 was already in use on the dev machine) |
| Cache/queue backing | Redis 7 |
| Container orchestration | `docker-compose.yml` - `app`, `mysql`, `redis` services |
| ORM connection | `DATABASE_URL` env var (DSN), parsed by `config/app_local.php` |
| Linting | `cakephp/cakephp-codesniffer` (installed as a dependency of `cakephp/app`), configured in `phpcs.xml` |
| Git hooks | Husky (`.husky/`), runs `phpcs` inside the app container plus `osv-scanner` on the host - needs Node/npm on the host, `osv-scanner` installed locally |
| Schema migrations | `cakephp/migrations` (Phinx-based), files in `config/Migrations/` |
| Auth | `cakephp/authentication` (session + form login) + `cakephp/authorization` (policy checks), wired in `src/Application.php` |

## Key directories

| Path | Purpose |
|---|---|
| `src/Controller/Admin/` | owner/staff dashboard, behind session auth. `AppController` (base, requires login + policy checks) and `BusinessesController`, `ServicesController`, `UsersController` (+ `signup`/`login`/`logout`), `BookingsController` |
| `src/Controller/AppController.php` | shared public base - disables the identity check by default; public controllers must explicitly `skipAuthorization()` since they have no policy |
| `src/Controller/` | public booking flow controllers (not yet built) |
| `src/Model/Table/` | query logic, associations, validation - `BusinessesTable`, `UsersTable`, `ServicesTable`, `AvailabilitiesTable`, `CustomersTable`, `BookingsTable`, `PlansTable` built (full domain model in place) |
| `src/Model/Entity/` | data objects - `Business`, `User`, `Service`, `Availability`, `Customer`, `Booking`, `Plan` built |
| `config/Migrations/` | Phinx-based schema migrations |
| `templates/` | native `.php` views, mirrors Controller structure |
| `src/Model/Behavior/` | `TenantScopeBehavior` - attached to `Users`, `Services`, `Customers`, `Bookings` |
| `plugins/` | CakePHP plugins - `TenantScope` planned as an extraction target (Phase 6) |
| `bin/cake` | console entry point, the `artisan` equivalent |

## Domain model

| Table | Key columns | Associations |
|---|---|---|
| `businesses` | `slug` (unique), `plan_id` (nullable FK), `stripe_customer_id`, `subscription_status`, `trial_ends_at` (all nullable) | `belongsTo Plans`; `hasMany Users, Bookings` |
| `users` | `business_id` (FK), `email` (unique), `role` (`owner`/`staff`) | `belongsTo Businesses`; `belongsToMany Services` (via `services_users`); `hasMany Availabilities, Bookings` |
| `services` | `business_id` (FK), `duration_minutes`, `price` | `belongsTo Businesses`; `belongsToMany Users` (via `services_users`); `hasMany Bookings` |
| `availabilities` | `user_id` (FK), `day_of_week`/`date` (mutually exclusive), `start_time`/`end_time`, `is_available` | `belongsTo Users` |
| `customers` | `business_id` (FK), `email` (unique per business) | `belongsTo Businesses`; `hasMany Bookings` |
| `bookings` | `business_id`, `service_id`, `user_id`, `customer_id` (all FK), `status` (`pending`/`confirmed`/`cancelled`/`completed`), `reminder_sent_at` (nullable) | `belongsTo Businesses, Services, Users, Customers` |
| `plans` | `staff_limit`, `stripe_price_id` (nullable), `price` | `hasMany Businesses` |

Admin CRUD (`src/Controller/Admin/`, routed under the `Admin` prefix added in `config/routes.php`) is baked for Businesses, Services, Users, Bookings and now sits behind session auth - see Notable decisions for what's still open (tenant/policy wiring per action).

## Notable decisions

- **Stack diverges from the project template's Laravel default.** The brief explicitly specifies CakePHP 5.x; RULES.md's default stack only applies when a brief doesn't state otherwise.
- **FrankenPHP kept despite being Laravel-ecosystem-associated**, since it still works as a plain PHP runtime; the container runs `bin/cake server` rather than an artisan-equivalent.
- **MySQL host port is 3307, not 3306** - 3306 was already bound on the dev machine at setup time.
- **Composer is copied into the Dockerfile from the official `composer:2` image** - the base FrankenPHP image doesn't ship it.
- **Database connection uses `DATABASE_URL`** rather than the scaffold's default array-based `Datasources.default` config, so container and CI environments can override it with one variable.
- **Multi-tenancy is single-database, shared-schema**, enforced via a `business_id` column plus `TenantScopeBehavior` (`src/Model/Behavior/TenantScopeBehavior.php`), the Table-level equivalent of a Laravel Eloquent global scope. Attached to `UsersTable`, `ServicesTable`, `CustomersTable`, `BookingsTable` (every table with a direct `business_id` column - `AvailabilitiesTable` is scoped indirectly via `user_id` and doesn't carry the column itself, so it's not attached there).
- **`TenantScopeBehavior`'s tenant id is set explicitly via `setTenantId(int $id)`**, never read from a global or session inside the behavior itself - this keeps it unit-testable in isolation and makes every scoped query traceable to a concrete id. It hooks `Model.beforeFind` (adds a `WHERE business_id = ?`) and `Model.beforeSave` (stamps the tenant id on new entities, refuses to save a mismatched one). Calling `find()` or `save()` on a scoped table without calling `setTenantId()` first throws a `RuntimeException` rather than silently returning unscoped/cross-tenant data - this is deliberate fail-loud behaviour, not a bug. `tests/TestCase/Model/Behavior/TenantScopeBehaviorTest.php` uses hand-written two-tenant fixtures (not `bake`-generated) to prove cross-tenant isolation, since this is the one piece the brief flags as needing real tests from the start.
- **Session-based auth is wired** (`cakephp/authentication` + `cakephp/authorization`, installed and configured in `src/Application.php`). One thing is deliberately still open, scoped to the next task rather than patched here: Admin CRUD actions (Businesses/Services/Bookings index etc.) don't yet call `setTenantId()` on their tables (500s with `TenantScopeBehavior`'s guard message) or `$this->Authorization->authorize()` (500s with "did not apply any authorization checks") - both need the logged-in identity's `business_id`/role, which is the Policy classes task. Do not add a hardcoded/stopgap tenant id or skip these checks to make the pages render early.
- **Signup (`Admin/UsersController::signup`, unauthenticated) creates a `Business` and its first `User` (role `owner`) in one DB transaction** (`$connection->transactional()`) - the `User` entity is only constructed once the `Business` has saved and has an `id`, so its `business_id` is always known and validated normally (no skipped validation, no false "business_id required" error against data that hasn't been assigned yet). `TenantScopeBehavior::setTenantId()` is called with the just-created business's id right before the `User` save.
- **`User::_setPassword()` hashes on assignment** via `Authentication\PasswordHasher\DefaultPasswordHasher` - any `set('password', ...)` or mass-assignment through `newEntity()`/`patchEntity()` hashes automatically; nothing else in the app should hash a password manually.
- **Login (`Admin/UsersController::login`) looks a `User` up by email before knowing their tenant** - this is the one legitimate place a lookup must cross tenants by design. `TenantScopeBehavior::findUnscoped()` (a custom `find('unscoped')` finder, `implementedFinders`) is the explicit, tested escape hatch; the Password identifier's `OrmResolver` is configured with `'finder' => 'unscoped'` in `Application::getAuthenticationService()` to use it. This is the only place in the app that should ever call `find('unscoped')`.
- **Authorization is structured so public controllers skip it explicitly, rather than one shared default skip.** `AppController` (public base) disables the identity check but does *not* call `skipAuthorization()` - each public controller (currently just `PagesController`) calls it itself in its own `initialize()`. `Admin\AppController` re-enables `requireIdentity` and allowlists only the `login` action. This means a new controller that forgets to either authorize or skip fails loudly (`AuthorizationMiddleware`'s end-of-request check) rather than silently passing.
- **No Cashier-equivalent exists in CakePHP** - Stripe billing (Phase 5) will integrate `stripe/stripe-php` directly rather than through a framework wrapper.
- **`declare(strict_types=1)` is enforced via `SlevomatCodingStandard.TypeHints.DeclareStrictTypes`** in `phpcs.xml`, the PHPCS equivalent of Pint's `declare_strict_types` setting - PHPCS has no built-in flag for this, so the Slevomat sniff (already pulled in transitively by `cakephp/cakephp-codesniffer`) fills the gap and is `phpcbf`-fixable.
- **The `app` container runs as the host UID/GID** (`user: "${UID:-1000}:${GID:-1000}"` in `docker-compose.yml`, sourced from a gitignored root `.env`) - without this, `bin/cake bake`/`migrations create` write root-owned files into the bind-mounted project, which the host user then can't edit or delete.
- **`COMPOSER_HOME=/tmp/composer`** is set on the `app` service - the non-root UID has no writable `$HOME` for Composer's own cache directory otherwise (`composer require` still works without it, just with a cosmetic cache warning on every run).
- **`bake`-generated stub fixtures and controller/table tests are deleted immediately after baking** rather than kept as empty placeholders - they assert nothing (`markTestIncomplete`) and reference fixtures that don't exist once deleted.
- **The "Staff can perform a Service" relationship (brief's `belongsToMany Staff`) is modelled as `Services belongsToMany Users`** via a `services_users` join table - there's no separate Staff entity, "staff" is just a `User` with `role = 'staff'`, and `bake` names the association after the actual target table.
- **`availabilities`' day-of-week/date mutual exclusivity is enforced in `AvailabilitiesTable::buildRules()` rather than `validationDefault()`** - CakePHP's field-level `allowEmpty*` skips all rules (including custom ones) for a field once it's empty, which breaks a validator-level "neither set" check. `buildRules()` runs against the full entity regardless of individual field emptiness, so it's the correct layer for cross-field invariants like this.
- **`businesses.plan_id`'s FK uses `RESTRICT` (not `CASCADE`) on delete** - unlike the other CASCADE relationships where child rows become meaningless once the parent is gone, a `Plan` shouldn't be deletable while businesses are still subscribed to it.
- **The full domain model is in place** (see table above) with all associations wired in both directions and `TenantScopeBehavior` attached to every tenant-scoped table.

## External integrations

None yet. Planned: Stripe (`stripe/stripe-php`, Phase 5), `cakephp/queue` (Phase 4 upgrade).
