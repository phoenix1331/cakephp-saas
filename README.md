# cakephp-saas

<img width="2850" height="1618" alt="Screenshot 2026-09-22 192517" src="https://github.com/user-attachments/assets/6bb3e860-5c9c-4ab6-84f1-29621ce257db" />

A booking and scheduling SaaS for solo service businesses (hairdressers, tutors, therapists), built in CakePHP 5.

## What this demonstrates

This project is a deliberate framework-learning exercise: every major piece is built the CakePHP way and mapped against its closest Laravel equivalent, covering the concepts a Laravel developer would need to translate to work productively in CakePHP.

- **Multi-tenancy as a Table behavior** - `TenantScopeBehavior` is CakePHP's answer to a Laravel Eloquent global scope: it hooks `Model.beforeFind`/`Model.beforeSave` to enforce single-database, shared-schema tenant isolation, fails loudly (`RuntimeException`) rather than silently leaking cross-tenant data, and ships with an explicit, tested escape hatch (`find('unscoped')`) for the handful of legitimate cross-tenant lookups (login, the public booking flow).
- **The plugin system as a real extraction target, not just a demo** - `TenantScopeBehavior` was pulled out of the app into its own `plugins/TenantScope` CakePHP plugin, with its own namespace, autoload entry, PHPCS scan path and PHPUnit testsuite - the full ceremony a reusable piece of framework code actually needs, not just a file move.
- **Authentication and authorization as separate concerns** - `cakephp/authentication` (who are you) and `cakephp/authorization` (what are you allowed to do) are two different plugins wired independently, resolved via policy classes (`BusinessPolicy`, `ServicePolicy`, `BookingPolicy`, `UserPolicy`) rather than a single combined guard/gate layer.
- **Computation kept out of the ORM layer** - `SlotFinder` (available slot computation) and `IcsBuilder` (hand-rolled RFC 5545 `.ics` generation) are both plain PHP classes, not Table/Behavior classes, since they're computation over persisted data rather than persistence itself.
- **A real Stripe integration without a Cashier equivalent** - CakePHP has no billing package, so Checkout, the Customer Portal, and signature-verified webhook handling are all built directly against `stripe/stripe-php`, behind a narrow `CheckoutClientInterface` that keeps the integration swappable and testable.

## Tech stack

| Layer | Choice |
|---|---|
| Framework | CakePHP 5.4 (PHP 8.4) |
| Database | MySQL 8.4 |
| Cache / queue backing | Redis 7 (`cakephp/queue`) |
| Runtime | FrankenPHP, via `bin/cake server` |
| Billing | Stripe (`stripe/stripe-php`) |
| CSS | Tailwind CSS v4 |
| Auth | `cakephp/authentication` + `cakephp/authorization` |
| Linting | `cakephp/cakephp-codesniffer` (PHPCS), with a custom `declare(strict_types=1)` sniff |

## Getting started

### Prerequisites

- Docker and Docker Compose
- Node/npm on the host, for Tailwind CSS builds and Husky git hooks (the FrankenPHP image has no Node)
- [`osv-scanner`](https://github.com/google/osv-scanner) installed locally (`go install github.com/google/osv-scanner/cmd/osv-scanner@latest`) - the pre-commit hook runs a dependency audit with it
- `gh auth login` run once, if using the GitHub MCP server
- For the MySQL MCP server: export `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_DATABASE` to match this project's `docker-compose.yml` MySQL service (`127.0.0.1:3307`, user `root`, password `root`, database `cakephp_saas` - port 3307 because 3306 was already bound on the original dev machine)
- Playwright and Context7 MCP servers need no extra setup beyond `npx` being available

### Setup

1. Copy `.env.example` to `.env` and fill in Stripe test-mode credentials (`STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SECRET`) if you want billing to work locally - the app runs fine without them otherwise.
2. `make up` - start the `app` (FrankenPHP), `mysql` and `redis` containers.
3. `make migrate` - run database migrations.
4. `npm install` - installs Husky and activates the git hooks (`commit-msg`, `pre-commit`).
5. `make css` - build the Tailwind CSS bundle (already committed, so only needed after a template change).
6. Visit `http://localhost:8000` - redirects to sign-up/login. Sign up to create your first Business and owner account; the built-in 14-day trial needs no card up front.

### Other commands

| Command | What it does |
|---|---|
| `make down` | Stop and remove containers |
| `make build` | Rebuild the app image |
| `make shell` | Open a shell in the app container |
| `make test` | Run the PHPUnit suite (app + `TenantScope` plugin) |
| `make lint` / `make lint-fix` | Check / fix code style with PHPCS |
| `make logs` | Tail the app container's logs |
| `make fresh` | Drop and re-run all migrations |
| `make css-watch` | Rebuild Tailwind CSS on file changes |
| `make send-reminders` | Run the booking-reminder command once (see Architecture notes) |

## Environment variables

| Key | Description | Default |
|---|---|---|
| `UID` / `GID` | Host user/group id the `app` container runs as, so bind-mounted files stay host-owned | `1000` / `1000` |
| `STRIPE_SECRET_KEY` | Stripe secret API key (test mode) | none - billing calls fail without it |
| `STRIPE_WEBHOOK_SECRET` | Webhook signing secret, from `stripe listen` or the Stripe dashboard | none - webhook requests are rejected without it |

`DATABASE_URL` and `REDIS_URL` are fixed in `docker-compose.yml` rather than read from `.env`, since they only ever need to match the `mysql`/`redis` Compose services.

## Endpoints / request.http

`request.http` at the project root covers the public booking flow (`/book/{slug}`) and the Stripe webhook endpoint (`/webhooks/stripe`), with example payloads for each Stripe event type the app handles. Works with the VS Code REST Client and JetBrains HTTP Client extensions.

For real webhook testing (not just the illustrative signatures in `request.http`), use the Stripe CLI: `stripe listen --forward-to localhost:8000/webhooks/stripe`, then `stripe trigger checkout.session.completed`.

## Architecture notes

- **Multi-tenancy is single-database, shared-schema**, not one database per tenant - every tenant-scoped table carries a `business_id` column, enforced by `TenantScopeBehavior` (see `plugins/TenantScope/`). This trades tenant-level isolation for operational simplicity, appropriate for a SaaS aimed at solo businesses rather than enterprise customers with compliance requirements.
- **Reminders have two independent delivery paths that can both safely fire for the same booking**: `SendBookingReminderJob` is queued at booking-confirmation time via `cakephp/queue` (the primary path, needs a running `bin/cake worker`), and `SendBookingRemindersCommand` (`make send-reminders`) is a polling fallback safety net for when the queue worker is down. Both share the same idempotency guard (`reminder_sent_at IS NULL`), so running both is intentional, not redundant.
- **The Stripe Customer is created lazily**, on first Checkout attempt, not at signup - a Business may never actually subscribe, and the 14-day trial needs no card.
- **`TenantScopeBehavior` was extracted into its own CakePHP plugin** (`plugins/TenantScope/`) as a deliberate showcase of the plugin system - not because the app needs it distributed separately, but because it's the one piece of this codebase generic enough to be reusable elsewhere.
- **CONTEXT.md** at the project root has the full architecture reference (key directories, domain model, every non-obvious decision made along the way) for anyone picking this repo up cold.
