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

## Key directories

| Path | Purpose |
|---|---|
| `src/Controller/Admin/` | owner/staff dashboard - `BusinessesController`, `ServicesController`, `UsersController`, `BookingsController` baked (CRUD only, not yet behind auth - Phase 2) |
| `src/Controller/` | public booking flow controllers (not yet built) |
| `src/Model/Table/` | query logic, associations, validation - `BusinessesTable`, `UsersTable`, `ServicesTable`, `AvailabilitiesTable`, `CustomersTable`, `BookingsTable`, `PlansTable` built (full domain model in place) |
| `src/Model/Entity/` | data objects - `Business`, `User`, `Service`, `Availability`, `Customer`, `Booking`, `Plan` built |
| `config/Migrations/` | Phinx-based schema migrations |
| `templates/` | native `.php` views, mirrors Controller structure |
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

Admin CRUD (`src/Controller/Admin/`, routed under the `Admin` prefix added in `config/routes.php`) is baked for Businesses, Services, Users, Bookings - no auth gate yet, that's Phase 2.

## Notable decisions

- **Stack diverges from the project template's Laravel default.** The brief explicitly specifies CakePHP 5.x; RULES.md's default stack only applies when a brief doesn't state otherwise.
- **FrankenPHP kept despite being Laravel-ecosystem-associated**, since it still works as a plain PHP runtime; the container runs `bin/cake server` rather than an artisan-equivalent.
- **MySQL host port is 3307, not 3306** - 3306 was already bound on the dev machine at setup time.
- **Composer is copied into the Dockerfile from the official `composer:2` image** - the base FrankenPHP image doesn't ship it.
- **Database connection uses `DATABASE_URL`** rather than the scaffold's default array-based `Datasources.default` config, so container and CI environments can override it with one variable.
- **Multi-tenancy is single-database, shared-schema**, enforced via a `business_id` column plus a planned `TenantScopeBehavior` (Phase 1), the Table-level equivalent of a Laravel Eloquent global scope.
- **No Cashier-equivalent exists in CakePHP** - Stripe billing (Phase 5) will integrate `stripe/stripe-php` directly rather than through a framework wrapper.
- **`declare(strict_types=1)` is enforced via `SlevomatCodingStandard.TypeHints.DeclareStrictTypes`** in `phpcs.xml`, the PHPCS equivalent of Pint's `declare_strict_types` setting - PHPCS has no built-in flag for this, so the Slevomat sniff (already pulled in transitively by `cakephp/cakephp-codesniffer`) fills the gap and is `phpcbf`-fixable.
- **The `app` container runs as the host UID/GID** (`user: "${UID:-1000}:${GID:-1000}"` in `docker-compose.yml`, sourced from a gitignored root `.env`) - without this, `bin/cake bake`/`migrations create` write root-owned files into the bind-mounted project, which the host user then can't edit or delete.
- **Password hashing is deliberately not yet added to the `User` entity** - `cakephp/authentication` isn't installed until Phase 2, and its `DefaultPasswordHasher` is what the mutator will use.
- **`bake`-generated stub fixtures and controller/table tests are deleted immediately after baking** rather than kept as empty placeholders - they assert nothing (`markTestIncomplete`) and reference fixtures that don't exist once deleted.
- **The "Staff can perform a Service" relationship (brief's `belongsToMany Staff`) is modelled as `Services belongsToMany Users`** via a `services_users` join table - there's no separate Staff entity, "staff" is just a `User` with `role = 'staff'`, and `bake` names the association after the actual target table.
- **`availabilities`' day-of-week/date mutual exclusivity is enforced in `AvailabilitiesTable::buildRules()` rather than `validationDefault()`** - CakePHP's field-level `allowEmpty*` skips all rules (including custom ones) for a field once it's empty, which breaks a validator-level "neither set" check. `buildRules()` runs against the full entity regardless of individual field emptiness, so it's the correct layer for cross-field invariants like this.
- **`businesses.plan_id`'s FK uses `RESTRICT` (not `CASCADE`) on delete** - unlike the other CASCADE relationships where child rows become meaningless once the parent is gone, a `Plan` shouldn't be deletable while businesses are still subscribed to it.
- **The full domain model is in place** (see table above) with all associations wired in both directions. `TenantScopeBehavior` is the next piece, since every tenant-scoped table now exists for it to attach to.

## External integrations

None yet. Planned: Stripe (`stripe/stripe-php`, Phase 5), `cakephp/authentication` + `cakephp/authorization` (Phase 2), `cakephp/queue` (Phase 4 upgrade).
