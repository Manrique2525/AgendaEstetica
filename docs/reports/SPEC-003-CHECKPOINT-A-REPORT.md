# SPEC-003 Checkpoint A Report

## 1. Repository state before implementation

- Branch: `feat/spec-003-business-core`.
- Base: `ba67181`.
- SPEC-003: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Discovery: `COMPLETED`.
- Checkpoint A: authorized.
- Checkpoint B: not authorized.
- Working tree before implementation: clean.

## 2. Scope delivered

Checkpoint A implements only the approved Core persistence foundation:

- `BusinessProfile` singleton operational configuration.
- `BusinessHour` recurring weekly intervals.
- `ServiceCategory` and `Service` catalog foundations.
- `Professional` resources without authentication.
- `professional_service` compatibility relationship.
- `Customer` identity without accounts.
- `ServicePricingType` backed enum.
- Eloquent models, relationships and factories.
- MySQL constraints, foreign keys, indexes and uniqueness rules.
- Persistence integrity tests.

## 3. Persistence decisions implemented

- Seven approved tables, with the relationship pivot included in the set.
- `business_profiles.singleton_key` enforces one operational profile.
- `business_profiles.timezone` is stored as an IANA identifier without selecting a business value.
- Capacity is a typed field on `business_profiles`.
- Business hours use ISO weekdays `1-7`, ordered intervals and `TIME` values.
- Pricing supports `fixed`, `starting_from` and `variable` with nullable `DECIMAL(10,2)`.
- Category, service and professional lifecycle uses the `active` flag.
- Normalized customer phone is indexed and intentionally not unique.
- No global soft deletes, generic settings table or generic repository was added.

## 4. Out-of-scope audit

Not implemented:

- Appointment Engine, availability, overlap detection or capacity enforcement.
- Admin Agenda, public booking, CMS, notifications, WhatsApp or ecommerce.
- Controllers, API routes, Form Requests, Actions or application services.
- Customer or professional authentication.
- Phone normalization behavior or external phone libraries.
- Production seeders, real business data, prices, people or media.
- New dependencies or ADRs.

## 5. Acceptance evidence

- `tests/Feature/BusinessCore/PersistenceTest.php`: 8 tests, 37 assertions.
- Full Pest result after re-migration: 20 tests, 95 assertions, PASS.
- Tests cover singleton/profile capacity, weekly interval constraints, catalog/pivot uniqueness, pricing/duration invariants, foreign keys, casts and duplicate normalized customer phones.
- Persistence tests passed against the dedicated MySQL test database.

## 6. Testing database proof

The effective test configuration was verified without exposing credentials:

```text
APP_ENV: testing
DB_CONNECTION: mysql
DB_HOST: 127.0.0.1
DB_PORT: 3307
DB_DATABASE: agenda_estetica_test
DB_USERNAME: agenda_estetica_test
MySQL server: 8.4.11
```

The proof came from `php artisan --env=testing about`, `config:show`, `db:show` and a live `SELECT VERSION()` against the connection. `phpunit.xml`, `.env.testing`, `Tests\TestCase` and `tests/Pest.php` independently force or guard this database.

## 7. Migration lifecycle

- Fresh migration: `PASS`. Foundation and all six SPEC-003 migration files applied from zero.
- Schema verification: `PASS`. `business_profiles`, `business_hours`, `service_categories`, `services`, `professionals`, `professional_service` and `customers` exist.
- Rollback: `PASS`. The complete migration batch reverted cleanly in testing.
- Re-migration: `PASS`. The complete migration set applied again in testing.
- Full Pest after re-migration: `PASS`, 20 tests and 95 assertions.

The initial CLI issue was caused by running Artisan without `--env=testing`: Laravel loaded local `.env`, which targets the development database and credentials. PHPUnit did not have this problem because `phpunit.xml` forces testing variables and the test guards reject any other database. The safe resolution was to use `php artisan --env=testing ...`; no credentials, `.env.example`, or secrets in Git were changed.

## 8. MySQL constraint evidence

The live MySQL metadata confirms these checks exist:

| Table | Constraint | Rule |
| --- | --- | --- |
| `business_profiles` | `business_profiles_singleton_key_check` | `singleton_key = 1` |
| `business_profiles` | `business_profiles_capacity_check` | `max_simultaneous_clients > 0` |
| `business_hours` | `business_hours_weekday_check` | `weekday BETWEEN 1 AND 7` |
| `business_hours` | `business_hours_time_order_check` | `opens_at < closes_at` |
| `services` | `services_duration_check` | `duration_minutes > 0` |
| `services` | `services_pricing_check` | fixed/starting_from require non-negative price; variable requires NULL |

The tests use real MySQL writes and prove rejection of invalid singleton values, duplicate singleton rows, non-positive capacity, weekday values below 1 and above 7, reversed/equal hours, non-positive duration, negative price, fixed NULL price, starting-from NULL price, variable non-NULL price and unknown pricing type.

## 9. Invariant coverage matrix

| Invariant | Test exists | DB-level evidence |
| --- | --- | --- |
| singleton value guard | PASS | PASS |
| singleton uniqueness | PASS | PASS |
| positive capacity | PASS | PASS |
| weekday min/max | PASS | PASS |
| opens < closes | PASS | PASS |
| duplicate interval | PASS | PASS |
| multiple intervals allowed | PASS | PASS |
| category unique name | PASS | PASS |
| category active cast | PASS | PASS |
| service category FK | PASS | PASS |
| service duration positive | PASS | PASS |
| service scoped-name unique | PASS | PASS |
| same service name across categories | PASS | PASS |
| pricing fixed valid | PASS | PASS |
| pricing fixed NULL invalid | PASS | PASS |
| pricing starting_from valid | PASS | PASS |
| pricing starting_from NULL invalid | PASS | PASS |
| pricing variable NULL valid | PASS | PASS |
| pricing variable amount invalid | PASS | PASS |
| negative price invalid | PASS | PASS |
| unknown pricing type invalid | PASS | PASS |
| enum Eloquent cast | PASS | N/A, application cast |
| duplicate professional names allowed | PASS | PASS |
| professional active cast | PASS | N/A, application cast |
| pivot relationship | PASS | PASS |
| pivot duplicate rejected | PASS | PASS |
| pivot invalid FK rejected | PASS | PASS |
| reverse service to professional relation | PASS | N/A, Eloquent relation |
| customer persistence | PASS | PASS |
| duplicate normalized phone allowed | PASS | PASS |

Overlap is intentionally not rejected; overlapping business-hour intervals remain valid in Checkpoint A and belong to later scheduling behavior.

## 10. Quality gates

Passed during this checkpoint:

- `composer validate --strict`.
- `composer audit`.
- `vendor/bin/pint --test`.
- `vendor/bin/phpstan analyse`.
- `php artisan test`.
- `npm run typecheck`.
- `npm run lint`.
- `npm run test`.
- `npm run build`.
- `npm audit`.

Backend: 20 tests and 95 assertions passed. Frontend: 10 files and 24 tests passed.

## 11. Regression and scope audits

- `/api/v1/health`: existing test PASS, 200 JSON.
- `/api/v1/admin/auth/me` without `Accept`: existing test PASS, 401 JSON.
- Unknown `/api/v1/*` without `Accept`: existing test PASS, 404 JSON.
- Route list: 4 existing routes; no SPEC-003 CRUD/API routes.
- No Controller, Form Request, Action, API Resource, route, Vue page or component was added.
- No new production/demo seeder was added. Existing `DatabaseSeeder` was unchanged.
- Composer dependencies added: none.
- npm dependencies added: none.
- Factories contain synthetic test data only; no production seeding occurs.

## 12. Initial issue retained for traceability

Before corrective validation, migration CLI commands were attempted without the testing environment and failed with development credentials. That failure did not modify the testing database. It is resolved and superseded by the successful testing-only lifecycle above.

## 13. Security and privacy audit

- `users`, `professionals` and `customers` remain separate models and tables.
- No credentials, roles or login behavior were added for professionals or customers.
- Customer data is limited to name and display/normalized phone fields.
- No real customer, professional, service or pricing data was introduced.
- No external service or new runtime dependency was added.

## 14. Checkpoint result

- Checkpoint A: `COMPLETED`.
- SPEC-003: remains `IN PROGRESS`; it is not closed.
- Checkpoint B: `NOT AUTHORIZED`.
- Remote CI evidence: pending push.
