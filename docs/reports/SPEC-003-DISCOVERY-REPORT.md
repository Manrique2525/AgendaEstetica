# SPEC-003 Technical Discovery Report

## 1. Repository state

- Repository: `AgendaEstetica`.
- Discovery base: `ee1d4e8`.
- `main`: `0182d02`.
- Working tree at Discovery start: clean.
- SPEC-001: `CLOSED`.
- SPEC-002: `CLOSED`.
- SPEC-003: `READY FOR DISCOVERY`.
- Implementation: not authorized.

## 2. Discovery branch

```text
docs/spec-003-discovery
```

Created from `ee1d4e8` exactly. It has not been pushed yet during this report preparation.

## 3. Branch base

```text
ee1d4e8
```

The base is the published SPEC-003 definition commit on top of canonical main.

## 4. Documentation reviewed

Read completely:

- `AGENTS.md`.
- All documents listed by `CONTEXT_INDEX.md`.
- `docs/MASTER_TECHNICAL_SPEC.md`.
- `docs/architecture/ARCHITECTURE.md`.
- ADR-001 and ADR-002.
- `docs/domain/DOMAIN_RULES.md`.
- `docs/roadmap/ROADMAP.md`.
- `docs/testing/TEST_PLAN.md`.
- SPEC-001 and SPEC-002.
- SPEC-001 implementation report.
- SPEC-002 implementation report.
- SPEC-002 closure report.

## 5. Current backend structure

The current application is a small Laravel 13 foundation:

- `app/Models/User.php` is the only application model.
- `app/Actions/AuthenticateAdminAction.php` is the only application Action.
- Controllers are limited to health and admin authentication.
- `AdminLoginRequest` is the only Form Request.
- `AuthenticatedUserResource` is the only API Resource.
- `CreateAdminCommand` is the only business-adjacent console command, and it creates internal administrators only.
- `routes/api.php` contains health and admin auth only.
- `routes/web.php` serves the SPA shell and fallback.
- No domain modules, repositories, policies or business services exist.

This small baseline supports adding a focused domain layer without preserving legacy business conventions.

## 6. Current database conventions

- Laravel migrations use anonymous migration classes.
- Primary keys use Laravel `$table->id()`, producing unsigned BIGINT-style identities.
- Existing foreign-key usage is limited to nullable `sessions.user_id` with an index.
- Standard timestamps are used on `users`.
- Existing tables use Laravel’s default snake_case names.
- The MySQL connection uses `utf8mb4`, configurable collation, strict mode and prefix indexes.
- Tests force MySQL database `agenda_estetica_test` on port `3307`.
- `Tests\TestCase` and Pest feature hooks fail if tests use another database.
- Existing Foundation persistence consists only of users/session, cache and queue tables.

## 7. Current test conventions

- Pest feature tests extend the Laravel TestCase and use `RefreshDatabase`.
- Feature tests are guarded against non-test persistence.
- Factories currently exist only for `User`.
- API tests mostly use JSON helpers; Discovery recommends adding raw-request cases when header-independent behavior matters.
- Static analysis uses Larastan level 5 configuration.
- Frontend tests use Vitest, Vue Test Utils and jsdom.

## 8. Single-business architecture assessment

The project models one real business, Salón y Barbería Yaris. No evidence supports a SaaS or multi-tenant architecture.

`BusinessProfile` is the recommended domain root for identity and operational configuration. It is not a tenant and must not introduce tenant routing or cross-business authorization.

## 9. Multi-tenancy rejection/assessment

`businesses`, `tenant_id`, `company_id` and `organization_id` columns across every table are `REJECTED` for SPEC-003. They add cost without a current business requirement and would change the architecture beyond the approved scope.

The single-business root may own related configuration and relationships without becoming multi-tenant infrastructure.

## 10. Business operational configuration alternatives

Evaluated:

- A. typed singleton `business_profiles` table.
- B. separate typed `business_configuration` table.
- C. `businesses` root entity with related configuration.
- D. generic `settings(key, value)` table.

## 11. Recommended business configuration model

`RECOMMENDED: A`, a typed singleton `business_profiles` table containing confirmed identity references and operational configuration required by later modules.

It should own only structured domain values such as business name, slogan/reference identity, confirmed contact/location values, IANA timezone and global capacity setting. Weekly hours should be a typed child table because they have repeated interval semantics.

`REJECTED: D` generic key/value settings as the primary model. It weakens type safety, validation, discoverability and migration clarity.

## 12. Singleton lifecycle recommendation

Do not assume `id = 1` as an undocumented invariant. Discovery should verify a typed singleton key or equivalent constrained lifecycle, for example one stable `default` scope value, plus an explicit setup/admin operation.

The application must define behavior when the profile is missing. It must not silently seed real business data in a migration.

## 13. Business timezone persistence

Recommended conceptual field: nullable or required according to final setup lifecycle, storing an IANA identifier as a bounded string. Exact length and nullability require Discovery confirmation.

The value is business data, not `APP_TIMEZONE`; technical instants remain UTC.

## 14. Business timezone validation

Use native PHP/Laravel timezone APIs to validate actual IANA identifiers. No dependency is justified. A missing value should be an explicit incomplete-configuration state, not an inferred geographic default.

## 15. BusinessHours alternatives

Evaluated:

- A. one row with JSON weekly schedule;
- B. one row per weekday with open/close columns;
- C. one row per weekly interval.

## 16. Recommended BusinessHours model

`RECOMMENDED: C`, typed rows for recurring weekly intervals. Conceptual fields:

- business profile/configuration owner;
- ISO weekday number;
- interval order;
- opening local time;
- closing local time.

Multiple intervals remain representable without JSON parsing. A day with no rows can represent closed until exception modeling is explicitly added later.

## 17. Day-of-week representation

`RECOMMENDED: ISO weekday integer 1-7` with a documented convention. It is queryable in MySQL, simple for Laravel and does not require creating a PHP enum solely for persistence.

## 18. Closed-day representation

No interval rows represent a recurring closed day. This avoids inventing closed-day production data and leaves exception rules for a later scheduling scope.

## 19. Multiple-interval assessment

The interval model supports split schedules such as 09:00-13:00 and 16:00-20:00 without requiring a future schema rewrite. It does not implement availability or exception calendars.

## 20. ServiceCategory model

Recommended minimal conceptual fields:

- name;
- active/inactive state;
- display order only if a real consumer needs ordering;
- timestamps.

Slug, icon, image, SEO and marketing description are not Core requirements.

## 21. ServiceCategory constraints/lifecycle

Discovery should decide name uniqueness with MySQL collation/case behavior. Active/inactive is preferred to deletion while future references may exist. Soft delete is not a default.

## 22. Service model

Recommended conceptual fields:

- category reference, with nullable/required policy to be decided;
- name;
- short operational description if a consumer needs it;
- positive duration in integer minutes;
- pricing type and nullable amount according to the pricing decision;
- active state;
- optional display order;
- timestamps.

No actual service names or prices are seeded.

## 23. Service duration model

`RECOMMENDED: positive integer minutes`. It maps directly to appointment calculations and avoids treating a business duration as a clock time. Exact lower/upper validation belongs to Discovery and domain approval.

## 24. Service description boundary

A short operational description may belong to Service if future admin/public consumers need it. Rich marketing sections, gallery, SEO copy and arbitrary content belong to CMS/Landing.

## 25. Pricing alternatives

Evaluated:

- A. nullable amount only;
- B. amount plus starting-from boolean;
- C. typed pricing type plus nullable amount;
- D. separate pricing entity.

## 26. Recommended pricing model

`RECOMMENDED: C`, a small typed pricing mode with minimum values `fixed`, `starting_from` and `variable`, plus a nullable amount.

The model supports “Desde $” and services without a public fixed amount without creating a pricing engine. Discovery must confirm exact constraints and naming before implementation.

## 27. Money representation

`RECOMMENDED: MySQL DECIMAL with Laravel decimal handling`, pending precision/scale confirmation. It avoids a package and is sufficient for a single-currency salon core. A Money value object is deferred unless rounding/reporting complexity proves it necessary.

## 28. Currency-field decision

`RECOMMENDED: no per-service currency column` for the single-business MXN scope. Currency can be a later configuration decision if multi-currency evidence appears; no generalization is justified now.

## 29. Pricing enum assessment

`RECOMMENDED: PHP backed enum` if implementation confirms typed pricing mode. Minimum candidates are `fixed`, `starting_from` and `variable`; discounts, promotions, packages and dynamic pricing are deferred.

## 30. Professional model

Recommended conceptual fields:

- display name;
- active state;
- optional display order if a consumer needs it;
- timestamps.

No email, phone, salary, commission, password, role, biography, social profile or photo is justified.

## 31. Professional lifecycle

`RECOMMENDED: active/inactive first, no default SoftDeletes`. Future appointments need historical references; deletion and anonymization require separate evidence.

## 32. ProfessionalService model

Recommended pivot/table: `professional_service` with professional and service foreign keys, timestamps only if relationship history needs them. No custom duration, price, commission, priority or skill field is justified.

## 33. ProfessionalService constraints

Protect the pair with a unique `(professional_id, service_id)` constraint and indexes supporting both directions. Active relationship state is `DISCOVERY REQUIRED`; do not add it by default.

## 34. Customer minimal model

Recommended conceptual fields:

- name;
- canonical phone/WhatsApp identity;
- timestamps.

No account, password, email, address, birth date, gender, social profile, medical data, marketing score or notes by default.

## 35. Customer phone representation

`RECOMMENDED: canonical E.164-like normalized string for operational lookup`, with display formatting handled at presentation boundaries. Whether a separate raw display value is necessary requires evidence; do not store duplicate representations without a consumer.

## 36. Phone normalization strategy

Prefer a narrowly documented normalization strategy compatible with the known Mexican business context and future WhatsApp use. Discovery must verify edge cases and whether the project can safely support them without a library.

## 37. Phone dependency assessment

`RECOMMENDED: zero dependency initially`. If robust international parsing becomes necessary, a future development-approval decision must evaluate a maintained phone library, licensing, payload and supported regions before installation.

## 38. Phone uniqueness decision

`RECOMMENDED: indexed, not globally unique by default`. Shared family or household numbers and changing contact details make unconditional uniqueness unsafe. Duplicate resolution is a future operational policy, not a Core constraint.

## 39. Customer lookup strategy

Future operations should prefer canonical phone lookup with explicit duplicate handling. No automatic customer merge or fuzzy identity matching is in scope.

## 40. Customer lifecycle/privacy strategy

`RECOMMENDED: preserve identity references needed by future appointments/orders; do not default to hard delete`. Anonymization/retention policy requires business/legal input and remains deferred. No compliance workflow is invented.

## 41. Global-capacity ownership

Capacity configuration belongs to the typed business operational configuration root. Appointment Engine owns enforcement and conflict resolution.

## 42. Capacity persistence recommendation

`RECOMMENDED: typed capacity field on the business operational configuration root`, subject to Discovery verification of lifecycle and ownership. A dedicated table is an alternative only if configuration history or independent lifecycle requires it. Generic key/value is rejected.

## 43. Capacity validation

Store a positive integer. The reference value up to 8 is business data, not a hard schema maximum. Appointment Engine later validates and enforces it under concurrency.

## 44. Generic-settings-table assessment

`REJECTED as default`. A catch-all table weakens type safety, discoverability, constraints and future migrations. Typed domain configuration is recommended.

## 45. Recommended exact table set

Discovery recommendation, subject to review:

- `business_profiles` as a typed singleton operational root;
- `business_hours` for recurring weekly intervals;
- `service_categories`;
- `services`;
- `professionals`;
- `professional_service`;
- `customers`.

No table is created by Discovery.

## 46. Table/column conceptual matrix

| Table | Conceptual fields | Notes |
| --- | --- | --- |
| `business_profiles` | identity/contact/location references, IANA timezone, capacity | typed singleton; no CMS content |
| `business_hours` | profile FK, weekday, interval order, opens_at, closes_at | recurring weekly intervals |
| `service_categories` | name, active, optional display order | no marketing fields |
| `services` | category FK, name, description if needed, duration, pricing mode/amount, active, optional order | no actual seeded data |
| `professionals` | name, active, optional order | resource, not user |
| `professional_service` | professional FK, service FK | unique pair |
| `customers` | name, canonical phone, timestamps | no account/auth |

## 47. PK/FK conventions

Use existing Laravel BIGINT-style IDs unless Discovery finds a concrete exception. Foreign keys should follow existing snake_case naming and explicit ownership. No UUID/ULID change is justified.

## 48. FK delete policies

Recommended initial posture:

- Protect business root deletion.
- Avoid cascading deletion of entities that future appointments/orders may reference.
- Use restrict/protect for service/professional historical references.
- Use cascade only for dependent configuration rows whose owner is never independently historical.

Exact policies require Discovery and relationship-history analysis.

## 49. Unique constraints

Candidates requiring Discovery confirmation:

- professional/service pair;
- business singleton scope;
- service/category names under MySQL collation;
- weekly interval identity;
- no unconditional customer phone uniqueness.

## 50. CHECK constraints

Evaluate stable database checks for:

- positive duration;
- positive capacity;
- non-negative amount;
- opening time before closing time.

Use application validation for complex cross-row or temporal logic. Do not encode appointment logic in MySQL checks.

## 51. Index strategy

Only query-backed indexes are recommended:

- active state filters where common;
- category/service lookup;
- professional/service pivot lookup in both directions;
- canonical customer phone lookup;
- business profile ownership and weekday hours lookup.

Do not index every column.

## 52. Cast strategy

Potential casts include boolean active flags, decimal amount handling, time values and a PHP pricing enum if approved. Exact casts require implementation Discovery and must follow Laravel conventions.

## 53. PHP enum recommendations

`ServicePricingType` is a plausible enum if the pricing model is approved. Do not create enums for active flags, weekday integers or concepts where simple typed values are clearer.

## 54. Soft-delete decision matrix

| Concept | SoftDeletes | Active flag | Recommendation |
| --- | --- | --- | --- |
| Business profile/root | NO | N/A | Protect singleton configuration. |
| Business hours | NO initially | N/A | Replace/version rules; exceptions are deferred. |
| ServiceCategory | NO initially | YES | Preserve references; deactivate. |
| Service | NO initially | YES | Preserve future references; deactivate. |
| Professional | NO initially | YES | Preserve historical references; deactivate. |
| ProfessionalService | NO | optional/deferred | Protect pair integrity; no speculative lifecycle. |
| Customer | DISCOVERY REQUIRED | N/A | Consider retention/anonymization before deletion policy. |

## 55. Active/inactive strategy

Active/inactive is recommended for ServiceCategory, Service and Professional. It preserves historical references and avoids global soft-delete complexity. Customer lifecycle requires separate privacy/retention analysis.

## 56. Model namespace recommendation

Start with current Laravel convention `App\Models` if the implementation remains small. Introduce a domain namespace only when multiple related models/actions justify it; do not create ceremonial DDD layers.

## 57. Action-layer recommendation

Use Actions only for operations with meaningful normalization, invariants or multi-record writes, such as creating a service with relationships or configuring typed business settings. Simple reads/CRUD coordination need not be wrapped in generic services.

## 58. Transaction boundaries

Use transactions for multi-record creation/update such as entity plus relationship rows when atomicity is required. Do not wrap every simple read or single-row update in a transaction.

## 59. Concurrency considerations

Core should protect duplicate relationship insertion with database uniqueness. Customer duplicate races require an operational policy. Appointment overlap, capacity and availability concurrency remain Appointment Engine responsibilities.

## 60. Admin API necessity assessment

`RECOMMENDED: no mandatory API in SPEC-003 implementation by default`. Domain/persistence integrity can be established first. If future consumers require admin mutations, Discovery must prove the resource/action boundary; CRUD-for-CRUD is rejected.

## 61. Public API boundary

Public exposure of services, professionals, business hours or customers is deferred. Any later public endpoint belongs to Public Booking or another explicit consumer SPEC.

## 62. Frontend impact

`NONE by default`. No Vue pages, dashboard or admin CRUD screens are required for the Core foundation. A consumer can be proposed only after evidence.

## 63. Factory strategy

Use factories and test-only fixtures for automated tests if implementation needs them. Do not treat factory data as production business data.

## 64. Seeder strategy

`RECOMMENDED: no production/demo seeders in Core`. Confirmed business initialization requires an explicit setup/deployment decision separate from migrations.

## 65. Production initialization-data strategy

Evaluate an explicit setup command, controlled deployment seed or admin configuration flow after the schema is approved. Do not hardcode confirmed values into migrations merely because they are known.

## 66. Test strategy

Implementation should cover migrations/schema integrity, relationships, unique/check constraints, pricing validation, timezone validation, weekly hours, phone normalization, authorization if API exists and Actions where they contain rules.

## 67. Test-database isolation

Continue using `agenda_estetica_test` on MySQL 8.4 with the existing TestCase/Pest safeguards. Discovery and implementation must never run destructive tests against the development database.

## 68. Appointment Engine dependency map

Appointment Engine will consume service duration/pricing behavior, active service state, professional compatibility, active professionals, recurring business hours, business timezone, capacity configuration and customer identity. It owns availability, overlap, state, approval, rescheduling and enforcement.

## 69. Admin Agenda dependency map

Admin Agenda may consume professionals, services, categories, business hours, customer identity and appointment references. Core does not create agenda navigation or management UI.

## 70. Ecommerce dependency map

Ecommerce may reference Customer identity and later snapshot its own order data. Core does not add products, orders, pricing snapshots, inventory or commerce fields to Customer.

## 71. Privacy assessment

Core should store minimal PII, avoid marketing profiles, restrict logs, avoid public exposure of internal fields and defer retention/anonymization policy to a business/legal decision.

## 72. Security assessment

Keep existing Sanctum/admin boundaries, backend validation, mass-assignment protection, safe JSON errors and explicit admin authorization. No public write API or new auth architecture is recommended.

## 73. Auditability boundary

Preserve timestamps and references needed for future audit logs. Do not implement a generic audit system in Core unless a cross-cutting decision is separately approved.

## 74. RECOMMENDED decisions

- Single-business typed `business_profiles` root; no multi-tenancy.
- Typed recurring `business_hours` intervals.
- ServiceCategory, Service, Professional, ProfessionalService and Customer as shared concepts.
- Typed pricing mode plus nullable amount, subject to Discovery confirmation.
- Canonical phone lookup without unconditional uniqueness.
- Active/inactive lifecycle for services/categories/professionals.
- Domain/persistence foundation before API/UI consumers.
- No generic settings key/value table.

## 75. ALTERNATIVES

- Separate typed business configuration table instead of profile-owned fields.
- Dedicated typed capacity table if lifecycle/history requires it.
- Static weekly-hours rows per weekday instead of interval rows if multiple intervals are rejected by evidence.
- A maintained phone library if native normalization is demonstrably insufficient.
- Admin API in a later consumer SPEC rather than Core.

## 76. REJECTED approaches

- Multi-tenant `businesses` architecture.
- Generic catch-all settings table as default.
- Customer accounts/authentication.
- Professional accounts/roles.
- Hardcoded `id = 1` singleton without lifecycle protection.
- Full soft deletes on every entity.
- Appointment availability or capacity enforcement in Core.
- Business data in migrations without explicit initialization policy.

## 77. Recommended SPEC changes

Discovery recommends retaining the scope boundaries in SPEC-003 and clarifying:

- BusinessProfile is operational configuration, not CMS.
- Capacity persistence remains Discovery Required.
- Admin/UI/API consumers are not required by default.
- Customer consent/marketing concepts are deferred.
- Checkpoint D is API/domain-only if a consumer is proven, not an assumed UI checkpoint.

These recommendations are not applied to SPEC-003 during Discovery.

## 78. ADR assessment

No ADR is created. A future ADR may be warranted for generic cross-module settings, shared retention/soft-delete policy, new authorization architecture or another durable cross-cutting decision.

## 79. Discovery requirements

Discovery evidence must inspect current models/migrations, naming, IDs, API/Form Requests/Actions, entity relationships, pricing, phone/privacy, constraints, soft deletes, test isolation and future module dependencies. No implementation is allowed in Discovery.

## 80. Proposed implementation checkpoints

- A: approved domain model and persistence foundation.
- B: domain validation and Actions only where rules justify them.
- C: admin API boundary only if Discovery proves it belongs in Core.
- D: integrity/security/regression hardening; no assumed UI.
- E: tests, documentation and final audit.

Each remains proposed and requires later approval.

## 81. Scope-size reassessment

`APPROPRIATELY SIZED` only if limited to shared Core data/rules. It becomes too large if it includes availability, booking, agenda, CMS, ecommerce, notifications, reporting or broad admin UI.

## 82. Development-approval blockers

None at definition stage. Discovery is required before development approval; unresolved persistence/pricing/privacy decisions may become checkpoint-specific blockers later.

## 83. Business-data pending items

- Exact IANA business timezone.
- Workday exceptions and special hours.
- Real professionals and assignments.
- Real service categories, services, durations and prices.
- Deposit/cancellation/no-show policies.
- Final privacy/contact policies.

## 84. Deferred decisions

- Appointment states, availability, overlap, capacity enforcement and rescheduling.
- Deposit policy.
- Professional schedules/blocks/vacations.
- Notification and marketing consent.
- Public APIs.
- Admin CRUD UI.
- CMS and ecommerce fields.

## 85. Non-blocking decisions

- Exact class/namespace names.
- Exact Action names.
- Whether an admin API is implemented in Core or a later consumer scope.
- Ordering field details where no consumer exists.

## 86. Implementation-readiness assessment

```text
Discovery: COMPLETED
SPEC-003: READY FOR DISCOVERY (awaiting Discovery review)
Implementation: NOT AUTHORIZED
Application code changes: NONE
```

## 87. Files created

- `docs/reports/SPEC-003-DISCOVERY-REPORT.md`.

## 88. Files modified

None. SPEC-003 and roadmap remain unchanged during Discovery.

## 89. Application-code changes

None.

## 90. Commit

```text
docs: complete SPEC-003 technical discovery
```

## 91. Commit hash

Recorded after report review and commit creation.

## 92. Push result

Pending normal push to `origin/docs/spec-003-discovery`.

## 93. Working tree

Must be clean after the Discovery report commit and push.

## 94. SPEC-003 status

`READY FOR DISCOVERY`.

## 95. Implementation status

`NOT AUTHORIZED`.

## 96. Recommended next action

Review Discovery findings and resolve development-approval decisions before authorizing SPEC-003 implementation.
