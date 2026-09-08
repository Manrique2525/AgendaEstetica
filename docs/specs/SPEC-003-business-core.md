# SPEC-003 - Business Core

## SPEC ID

`SPEC-003`

## Title

Business Core

## Status

`APPROVED FOR DEVELOPMENT / IN PROGRESS`

Esta SPEC tiene aprobación de desarrollo para los Checkpoints A y B. El Checkpoint C y cualquier SPEC posterior no están autorizados.

## Objective

Definir el mínimo núcleo de información y configuración compartida que los módulos posteriores necesitarán para operar el salón, sin implementar todavía el motor de disponibilidad/citas, agenda administrativa, booking público, CMS, notificaciones, ecommerce o pagos.

El objetivo es congelar límites de dominio, ownership, invariantes, relaciones y decisiones de persistencia antes de escribir código de negocio.

## Context

SPEC-001 Foundation está `CLOSED` y SPEC-002 UX and Design System Foundation está `CLOSED`. El roadmap confirma que el siguiente bloque es `Business core`, seguido por `Appointment engine`, `Admin agenda`, `Public booking`, notificaciones, CMS y ecommerce.

La arquitectura existente es un modular monolith Laravel/Vue con backend autoritativo, API `/api/v1`, Sanctum SPA y MySQL 8.4 LTS. SPEC-003 debe añadir únicamente el lenguaje conceptual compartido por los módulos posteriores; no debe adelantar sus flujos.

## Problem

- Los módulos posteriores necesitan referencias compartidas para servicios, categorías, profesionales, clientes y configuración del negocio.
- Las reglas de autenticación administrativa ya existen, pero `users` no representa profesionales ni clientes.
- La información empresarial editable no debe mezclarse con `.env` técnico.
- El motor de citas necesitará duración, asignabilidad, horarios, timezone y capacidad, pero esas reglas no deben duplicarse entre módulos.
- Aún no existe una decisión aprobada sobre modelado de precios variables, soft deletes, settings tipados, consentimiento o auditoría de cambios.

## Dependencies

### Normative dependencies

- `AGENTS.md`.
- `docs/context/CONTEXT_INDEX.md`.
- `docs/context/PROJECT_CONTEXT.md`.
- `docs/context/BUSINESS_CONTEXT.md`.
- `docs/MASTER_TECHNICAL_SPEC.md`.
- `docs/architecture/ARCHITECTURE.md`.
- `docs/architecture/adr/ADR-001-adopt-laravel-13.md`.
- `docs/architecture/adr/ADR-002-adopt-mysql-8-4-lts.md`.
- `docs/domain/DOMAIN_RULES.md`.
- `docs/roadmap/ROADMAP.md`.
- `docs/testing/TEST_PLAN.md`.
- `docs/specs/SPEC-001-project-foundation.md`.
- `docs/specs/SPEC-002-ux-design-system.md`.
- `docs/reports/SPEC-001-IMPLEMENTATION-REPORT.md`.
- `docs/reports/SPEC-002-IMPLEMENTATION-REPORT.md`.
- `docs/reports/SPEC-002-CLOSURE-REPORT.md`.

### Technical baseline

SPEC-003 consumes and does not reimplement:

- Laravel 13 and PHP 8.3+.
- MySQL 8.4 LTS.
- Vue 3, TypeScript, Vite and the closed SPEC-002 Design System.
- Sanctum SPA sessions/cookies and API `/api/v1`.
- Pest, Pint, Larastan, Vitest, Vue Test Utils, ESLint, `vue-tsc` and GitHub Actions quality CI.

## Inputs from SPEC-001

- `users` represents internal administrators only.
- UTC is the technical application timezone.
- `business_timezone` must be a configurable IANA business value; the exact value is pending business data.
- Backend owns validation, authorization and state integrity.
- IDs and persistence must follow the existing Laravel/MySQL conventions after Discovery verifies them.
- No customers, services, professionals, appointments or business tables were created by Foundation.
- Database Queue, Storage, Sanctum and API boundaries must remain unchanged.

## Inputs from SPEC-002

- Existing Vue Design System is closed and provides `UiButton`, `UiInput`, `UiFormField`, `UiContainer` and `UiCard`.
- Public/admin layouts and technical pages are styled, but no business page or module exists.
- No new UI framework, global state library, icon dependency or runtime provider is approved.
- Future admin consumers must reuse the existing token and primitive system rather than creating a second visual layer.
- The official logo remains pending and is unrelated to the domain model.

## Domain Scope

SPEC-003 is proposed as a shared business-domain foundation with these boundaries:

- Core business identity and operational configuration that later modules consume.
- Service catalog foundations without ecommerce product behavior.
- Professional resource foundations without users, shifts or appointment scheduling.
- Customer identity foundations without customer accounts.
- Typed relationships and references needed by the future Appointment Engine.
- Conceptual admin management/API boundaries, without implementing UI in this definition task.

## Entities / Concepts In Scope

### BusinessProfile - IN

Purpose: represent confirmed business identity and operational contact values that are business data rather than deployment secrets.

Approved conceptual fields for implementation planning:

- standard `id`;
- singleton guard;
- business name;
- phone;
- IANA timezone;
- maximum simultaneous clients.

The slogan and presentation-only location information are confirmed business data but are not required operational invariants for this Core persistence model. No legal entity, tax data, email or incomplete street address may be invented.

BusinessProfile is operational identity/configuration only. It must not become CMS storage for hero content, marketing sections, promotions, gallery, testimonials, FAQ, social presentation, landing SEO copy or arbitrary page content. Those concepts belong to CMS/Landing.

### BusinessHours - IN, with boundary

Purpose: represent recurring business wall-clock availability as typed business data.

Approved conceptual model: recurring weekly interval rows owned by the business profile, with weekday, interval order, opening time and closing time. It does not include availability calculation, appointments or booking.

The confirmed reference `10:00-20:00 every day` is input data, not permission to seed production records automatically.

Holiday exceptions, temporary closures, professional shifts, vacations and schedule blocks are deferred to later scheduling/appointment scopes.

### BusinessTimezone - IN as configuration concept

Purpose: store a configurable IANA timezone used to interpret business wall-clock rules.

The exact timezone remains `BUSINESS-DATA PENDING`. SPEC-003 may define its type, validation and ownership, but must not select a geographic value.

### ServiceCategory - IN

Purpose: group salon services for future administration and public consumption.

Scope is classification only. No CMS content, landing sections or public endpoint is implemented here.

### Service - IN

Purpose: represent an offered salon service as an operational resource consumed by later appointment/admin modules.

Conceptual attributes to validate:

- name;
- description/content boundary;
- duration;
- active status;
- category relation;
- professional compatibility relation;
- pricing behavior without inventing actual prices.

The approved pricing model is a PHP backed `ServicePricingType` persisted as a string with `fixed`, `starting_from` and `variable` values plus a nullable `DECIMAL(10,2)` amount. Fixed and starting-from require a non-negative amount; variable requires a null amount. No currency column is needed for the single-business MXN context.

### Professional - IN

Purpose: represent a professional resource assignable to services and future appointments.

Conceptual attributes to validate:

- display name;
- active status;
- timestamps only if implementation conventions require them.

Professionals are not authenticated users and receive no credentials, permissions or login flow in SPEC-003.

### ProfessionalService - IN as relationship

Purpose: express which professionals can perform which services.

The relationship is shared domain data. Professional shifts, blocks, duration overrides and availability calculations remain deferred.

The approved persistence direction is a minimal pivot with `professional_id` and `service_id`, no relationship timestamps and no custom pricing/duration/commission/priority fields. Exact index/FK syntax remains an implementation concern after Discovery.

### Customer - IN as identity foundation

Purpose: represent a client who requests appointments or may later interact with commerce, without requiring an application account in V1.

Approved conceptual data should remain minimal and privacy-conscious:

- name;
- phone display/original representation;
- canonical normalized phone representation;
- no marketing profile or generic consent collection.

Customer authentication, passwords, roles and login are explicitly out of scope.

### Customer consent boundary

Marketing consent, WhatsApp promotional opt-in, notification preferences, campaign consent and opt-out workflows are deferred to Notification/Meta WhatsApp scopes. Core may retain only strictly necessary operational contact/consent metadata after a concrete consumer and privacy decision exist.

The approved customer contact direction is a display/input phone value plus a canonical normalized phone value for lookup. Normalization must trim, remove presentation separators, preserve an explicit `+`, convert an explicit `00` prefix, and normalize unprefixed 10-digit Mexican numbers to `+52` plus the digits. This is a conservative operational strategy, not full international numbering-plan validation; no phone library is added.

### CapacityConfiguration - IN as configuration concept

Purpose: hold the general capacity reference needed by later appointment rules.

The current business reference is up to 8 simultaneous clients. Its persistence representation is approved as a typed field on `business_profiles`; the Appointment Engine owns enforcement, conflict resolution and recalculation.

## Concepts Explicitly Deferred

### Appointment Engine - DEFERRED

- availability calculation;
- professional overlap detection;
- appointment state machine;
- `schedule_version` mechanics;
- rescheduling/cancellation flows;
- approval workflow;
- appointment history;
- capacity enforcement;
- reminder eligibility and scheduling.

### Professional scheduling - DEFERRED

- professional shifts;
- schedule blocks;
- exceptions;
- time-off;
- recurring availability.

These belong to Appointment Engine/Admin agenda analysis, even if Professional is defined as a resource here.

### DepositPolicy - DEFERRED TO APPOINTMENT ENGINE

The business may support none, fixed or percentage deposits, but the policy and its enforcement are appointment/booking concerns. SPEC-003 should preserve an extension point without implementing or choosing a production policy.

### Admin management UI - DEFERRED

The domain/API may be prepared conceptually, but no CRUD screens, dashboard, navigation or Vue pages are included.

### Public API consumers - DEFERRED

Public service/professional/customer exposure belongs to Public booking or another explicitly approved public SPEC. SPEC-003 does not define public endpoints by convenience.

### CMS - DEFERRED

Hero, promotions, gallery, FAQ, landing sections and editable marketing copy belong to CMS/Landing.

### Ecommerce - DEFERRED

Products, variants, inventory, cart, orders, checkout, payments, reviews and favorites remain outside Core.

## Out-of-Scope Modules

- Appointment Engine and availability.
- Admin Agenda.
- Public Booking.
- Notification engine and WhatsApp.
- CMS/Landing.
- Products, inventory, cart, orders, checkout and payments.
- Reviews and favorites.
- Reporting, dashboards and analytics.
- Customer accounts, passwords and authentication.
- Professional accounts, roles and permissions.
- Business data invention, demo production seeders and commercial media.
- SPEC-004 or any later SPEC.

## BusinessProfile / CMS Boundary

Business Core may own structured identity and operational configuration. It does not own presentation content. The following remain CMS/Landing scope:

- hero content;
- marketing sections;
- promotional copy/banners;
- gallery;
- testimonials;
- FAQ;
- social-media presentation;
- landing SEO copy;
- arbitrary page content or commercial images.

## Business Rules

- `users`, `professionals` and `customers` remain separate concepts.
- Only internal administrators operate administrative management in V1; no role system is introduced.
- The backend is authoritative for all business validation and relationships.
- Business identity and configuration are not deployment secrets and must not live in `.env` by default.
- Business timezone is an IANA identifier and remains a pending business value.
- Services can have variable pricing behavior; no actual price is invented.
- Professionals are assignable resources, not auth principals.
- Customers do not require accounts in V1.
- Active/inactive state and ordering must be explicit if future consumers need them.
- No domain concept may silently become a CMS, appointment or ecommerce concern.

## Data Ownership

- `BusinessProfile` and business configuration: business administration ownership.
- `BusinessHours` and `BusinessTimezone`: business configuration ownership; interpretation by Appointment Engine later.
- `ServiceCategory` and `Service`: business administration ownership; consumption by appointment/public modules later.
- `Professional`: business administration ownership; schedule consumption later.
- `ProfessionalService`: business administration relationship; availability consumption later.
- `Customer`: operational business identity; no authentication ownership.
- `CapacityConfiguration`: business configuration ownership; enforcement by Appointment Engine.

## Relationships

Conceptual relationships to validate during Discovery:

- Service belongs to one category or an approved nullable category policy.
- Professional and Service are many-to-many through `ProfessionalService`.
- BusinessHours belongs to the business configuration context.
- BusinessTimezone belongs to the business configuration context.
- CapacityConfiguration belongs to the business configuration context.
- Future Appointment references Service, Professional and Customer without duplicating their definitions.

No relationship is implemented or migrated by this definition task.

## Key Invariants

- A professional cannot be represented as an administrative user.
- A customer cannot authenticate through the admin Sanctum flow.
- Names and contact values must have backend validation and normalization rules.
- Active/inactive semantics must be explicit and consistent across future consumers.
- Service duration must be a positive, domain-defined value before appointment availability uses it.
- Relationship duplicates must be prevented by a documented uniqueness rule.
- Business timezone must validate as an IANA identifier when supplied.
- Capacity values must be positive and bounded by a documented business rule.
- Actual pricing values remain business data and are never invented by development.

## Business-profile/settings assessment

`BusinessProfile` is approved as a typed singleton operational root because identity/contact/configuration values are business data distinct from `.env`. It is not a tenant, CMS container or generic settings table. A database-visible singleton guard is required; do not rely on `id = 1`.

Typed configuration concepts are preferred where semantics are known: profile, hours, timezone and capacity. Remaining settings require Discovery evidence before inclusion.

## Business-hours assessment

`BusinessHours` is approved as typed recurring weekly interval rows owned by `business_profiles`. Exception ownership is deferred; availability computation, shifts, blocks and enforcement belong to Appointment Engine.

The confirmed reference `10:00-20:00 every day` must not become an automatic production seed without explicit business-data confirmation.

## Business-timezone assessment

`BusinessTimezone` is proposed `IN` as an IANA-validated configuration concept. The exact value is `BUSINESS-DATA PENDING` and must not be inferred or seeded.

Technical instants remain UTC. Local recurring rules are interpreted later at domain boundaries.

## Professionals assessment

`Professional` and `ProfessionalService` are approved as base resources/relationships. The minimal Professional model is identity, active state and timestamps; names are business data supplied later. Shifts, blocks, schedules, permissions and login are deferred.

## Services assessment

`ServiceCategory` and `Service` are approved as base operational catalog concepts. Service duration, active state, category and professional compatibility are in scope. Pricing is `pricing_type` plus nullable amount with the approved modes `fixed`, `starting_from` and `variable`; actual service names/prices remain pending business data.

## Customers assessment

`Customer` is approved as a minimal identity foundation with name, display phone and canonical normalized phone. Customer accounts, authentication, passwords, roles and marketing consent remain out of scope.

## Capacity-settings assessment

`CapacityConfiguration` is approved as the typed `max_simultaneous_clients` field on `business_profiles`. No dedicated table or generic settings record is needed by default. Enforcement and conflict resolution belong to Appointment Engine. No production setting is seeded by this draft.

## Deposit-policy assessment

Deposit policy is `DEFERRED TO APPOINTMENT ENGINE`. The possible values none/fixed/percentage affect booking and confirmation behavior, so SPEC-003 should not choose or enforce them.

## Entity / Table Distinction

One domain concept does not necessarily equal one database table. Discovery must choose aggregate boundaries, normalization, ownership and lifecycle from domain evidence before migrations.

## Soft-delete Status

The approved matrix is:

| Concept | SoftDeletes | Reason |
| --- | --- | --- |
| BusinessProfile | NO | Protected singleton configuration. |
| BusinessHours | NO | Replace current weekly rules; exceptions are deferred. |
| ServiceCategory | NO | Preserve references and deactivate. |
| Service | NO | Preserve future references and deactivate. |
| Professional | NO | Preserve future references and deactivate. |
| ProfessionalService | NO | Minimal compatibility pivot. |
| Customer | NO for this SPEC | Retention/anonymization requires a future privacy decision. |

No global soft-delete architecture is approved.

## Active / Inactive Strategy Status

ServiceCategory, Service and Professional use an active/inactive lifecycle. BusinessProfile, BusinessHours, ProfessionalService and Customer do not receive an active flag by default.

## Persistence Recommendations

- Standard Laravel BIGINT-style IDs.
- `business_profiles` has a database-visible singleton guard with a constant unique scope value; no implicit `id = 1` rule.
- `business_hours` owns `business_profile_id`, ISO weekday 1-7, interval order, `opens_at` and `closes_at`.
- `business_hours` requires `opens_at < closes_at` and unique profile/weekday/open/close identity.
- `services` uses `DECIMAL(10,2)` amount and a string-backed pricing enum concept.
- `professional_service` has no timestamps or extra business fields by default.
- `customers.phone_normalized` is indexed but not unique.
- `ProfessionalService` pairs are unique and indexed in both useful lookup directions.
- No migration, model or enum is created by this definition.

## Database Impact

No database changes are authorized by this definition.

Future Discovery must evaluate conceptual tables/entities for:

- `business_profiles`;
- `business_hours`;
- `service_categories`;
- `services`;
- `professionals`;
- `professional_service`;
- `customers`.

These are the recommended conceptual table set, not implementation approval. Discovery already verified current Laravel migration/model naming, BIGINT conventions, foreign-key direction, indexes, uniqueness, timestamps and soft-delete implications at the recommendation level. No migration is designed or created here.

## API Impact

No API endpoints are authorized by this definition.

No API is required for SPEC-003 by default. Any future authenticated admin API under `/api/v1/admin/*` must be justified by a consumer SPEC or an approved scope amendment. Public exposure of services/professionals/customers is deferred to later modules.

Existing JSON error conventions, Sanctum sessions and backend authority remain unchanged.

## Frontend Impact

`NONE for this definition`.

Future implementation classification: `DOMAIN/PERSISTENCE FOUNDATION` first. No frontend, admin CRUD consumer, dashboard, navigation or public consumer is approved by default.

## Admin UI Boundary

Admin CRUD screens, dashboards, navigation and full management UI are deferred. SPEC-003 is `domain/API foundation` by default; a minimal technical admin consumer may be proposed only if Discovery proves it is required by the Core scope.

## Public API Boundary

Public services, professionals, customers and business configuration exposure are deferred to Public Booking or another approved consumer SPEC. No `/api/v1/public/*` endpoint is defined here.

## Security

- Preserve Sanctum SPA sessions/cookies and CSRF.
- No customer or professional accounts are introduced.
- Validate all business input in backend Form Requests/actions.
- Protect administrative operations with existing authentication and future explicit authorization rules.
- Prevent mass assignment of ownership, active state and sensitive fields.
- Keep errors safe and avoid leaking internal data.
- Do not log phone numbers or consent data unnecessarily.

## Privacy

- Customer phone/WhatsApp is personal data and requires data-minimization analysis.
- Store only fields required by approved future consumers.
- Define consent/contact metadata only when a concrete notification or booking use case exists.
- Do not expose internal customer/professional fields in public representations.
- No analytics, tracking or external provider is introduced.
- Retention/deletion policy remains a business/legal pending decision.

## Authorization

- Existing `users`/Sanctum admin authentication remains the entry point.
- Initially only Yaris administration is required.
- No roles, permissions, teams or professional accounts are introduced.
- Admin API authorization must be explicit and backend-owned when implementation begins.
- Public/customer actions are deferred to Public Booking and related SPECs.

## Validation

- Form Requests validate API input at the boundary.
- Actions/services enforce cross-entity invariants.
- Names, active states, ordering, relationships and identifiers require domain-specific validation.
- Phone/WhatsApp normalization and consent semantics require Discovery decisions.
- Timezone values must validate as IANA identifiers.
- Money/pricing representation must be resolved before Service implementation.

## Concurrency / Transactions

Discovery must identify operations that change multiple related records, such as service-professional relationships or settings with dependent invariants.

Use `DB::transaction` only where a concrete multi-record atomicity requirement exists. Do not add locking or generic transaction wrappers to simple CRUD operations. Appointment overlap/capacity concurrency is explicitly deferred.

## Auditability

SPEC-003 should remain compatible with future audit logs and preserve created/updated timestamps and relationship history where needed. It does not implement a generic audit system.

Sensitive administrative changes may later emit audit events, but the exact event model is a separate decision if cross-cutting/auditable behavior becomes required.

## Testing Strategy

Future implementation tests should include, where the selected scope requires:

- Feature/API tests for authentication, authorization, validation and response contracts.
- Model/relationship integrity tests.
- Action/service tests only for isolated rules with useful behavior.
- Database constraint and uniqueness tests against `agenda_estetica_test` on MySQL 8.4.
- Tests for normalization and pending business configuration validation.
- No tests for deferred Appointment Engine, CMS, ecommerce or public booking flows.
- Frontend tests only if a real consumer is approved; no UI implementation is authorized by this draft.

## Quality Gates

Inherit the Foundation gates:

```text
composer validate --strict
composer audit
vendor/bin/pint --test
vendor/bin/phpstan analyse
php artisan test
npm run lint
npm run typecheck
npm run test
npm run build
npm audit
GitHub Actions backend and frontend jobs
```

Not every gate may need new tests in a backend-only increment, but Foundation regression must remain green and unexecuted results must be reported honestly.

## Risks

### R-301: Business Core scope grows into Appointment Engine

Mitigation: keep schedules, availability, overlap, state transitions, booking approval and capacity enforcement explicitly deferred.

### R-302: Generic settings become an untyped dumping ground

Mitigation: require typed concepts and a Discovery-backed persistence decision; no generic key/value table by default.

### R-303: Personal data is over-modeled

Mitigation: minimal Customer identity, privacy review and no customer accounts.

### R-304: Pricing semantics are ambiguous

Mitigation: resolve variable pricing representation before Service implementation; do not invent prices.

### R-305: Professional ownership is confused with authentication

Mitigation: preserve `professional != user` and do not add credentials or roles.

### R-306: Soft deletes damage references/uniqueness

Mitigation: decide per entity after relationship/history analysis; do not apply globally.

### R-307: Core entities are exposed publicly too early

Mitigation: classify admin-only/future-public/internal representations before endpoints.

## Resolved Discovery Decisions

- Single-business typed `business_profiles` root; no multitenancy.
- Seven-table conceptual set: `business_profiles`, `business_hours`, `service_categories`, `services`, `professionals`, `professional_service` and `customers`.
- Weekly interval rows for BusinessHours with ISO weekday and no exception calendar.
- Typed capacity field on `business_profiles`; no capacity table by default.
- `fixed`, `starting_from` and `variable` pricing modes with nullable `DECIMAL(10,2)` amount.
- Canonical normalized customer phone indexed but not unique; no phone dependency.
- Active/inactive lifecycle for categories, services and professionals; no global soft deletes.
- No mandatory SPEC-003 API or frontend consumer.
- No generic settings table, generic repositories, UI framework or new dependency.

### Development-Approval Blockers

- None.
- Checkpoint A implementation is authorized. The remaining checkpoints require separate explicit authorization.

### Business-Data Pending

- Official business timezone.
- Definitive working days/exceptions.
- Real professionals and their service assignments.
- Definitive services, durations and prices.
- Deposit/cancellation/no-show policies.
- Final contact/policy data beyond the confirmed values.

### Deferred to Later SPECs

- Appointment states, availability, overlap, capacity enforcement, schedules, blocks and rescheduling.
- Deposit, cancellation, no-show and notification consent policies.
- Public APIs, Admin Agenda UI, CMS and ecommerce consumers.

### Non-Blocking

- Exact namespaces/folder names after Discovery verifies current conventions.
- Exact action/class names.
- Exact ordering field semantics where no consumer requires it yet.

## ADR Assessment

No ADR is created by this definition task.

Potential ADR candidates only if Discovery proves a durable cross-cutting decision:

- A generic settings architecture used across multiple modules.
- A shared soft-delete/retention policy across core entities.
- A new authorization architecture beyond the existing Yaris admin model.
- A persistence strategy that changes the approved modular-monolith boundaries.

Typed domain entities within the existing Laravel/Eloquent architecture do not automatically require an ADR.

## Acceptance Criteria

The following criteria are the full SPEC implementation criteria. Checkpoint A evidence covers only the persistence-related subset; deferred criteria remain pending:

1. SPEC-003 scope is implemented without Appointment Engine, Admin Agenda, Public Booking, CMS, ecommerce, WhatsApp or payments.
2. `users`, `professionals` and `customers` remain separate concepts, with no customer/professional authentication.
3. In-scope entities and relationships are implemented from an approved conceptual model, not invented directly from tables.
4. Business settings are separated from `.env` technical configuration.
5. Business timezone validates as an IANA identifier without inventing the final business value.
6. Service duration, active state, categorization and professional compatibility are represented according to the approved model.
7. Variable/starting-price behavior is represented according to an approved money/pricing decision without seeded real prices.
8. Professional-service relationships enforce approved uniqueness and integrity rules.
9. Customer identity is minimal, privacy-reviewed and does not create an application account.
10. Capacity configuration is separated from Appointment Engine enforcement.
11. Deposit policy remains deferred unless an approved scope decision moves it into this SPEC.
12. Backend validation, authorization, mass-assignment protection and safe JSON errors are covered.
13. Database constraints, indexes, timestamps and soft-delete decisions are documented and tested where applicable.
14. Tests run against the dedicated MySQL 8.4 test database and do not use development persistence.
15. Existing Foundation/SPEC-002 frontend and backend gates remain green.
16. No generic repository pattern, UI framework, unrelated dependency or business seed data is introduced.
17. Documentation, implementation report and remote CI evidence are complete.
18. Human acceptance is recorded before SPEC-003 changes from an in-progress status to closed.

## Definition of Done

- Scope is implemented only after SPEC-003 approval.
- Every Acceptance Criterion is PASS or has an explicitly approved exception.
- Domain model, ownership, relationships and invariants are documented.
- Database integrity and validation are tested against MySQL 8.4 test isolation.
- Backend authorization and safe API responses are tested.
- Existing auth, queue, storage, frontend Design System and API contracts remain intact.
- Composer/PHP and npm/frontend quality gates pass.
- Security, privacy, concurrency and auditability reviews are documented.
- No deferred module or business content is introduced.
- Implementation report and final audit report exist.
- Remote CI is green.
- Working tree is clean.
- Human acceptance is recorded.

## Discovery Requirements

Technical Discovery was completed in `docs/reports/SPEC-003-DISCOVERY-REPORT.md`. The resulting evidence and recommendations are:

- Existing users model/migration and auth boundaries.
- Existing technical migrations and naming/ID conventions.
- Laravel 13 model, Form Request and Action conventions.
- Current API route/response conventions.
- Current admin shell and closed Design System consumers.
- Domain entity candidates and relationship cardinality.
- BusinessProfile, BusinessHours, BusinessTimezone and CapacityConfiguration modeling.
- Service/category/professional/customer ownership and lifecycle.
- Pricing, phone normalization, consent and privacy implications.
- Indexes, uniqueness, timestamps and soft-delete consequences.
- PHP backed enum needs, without creating enums prematurely.
- Appointment Engine, Admin Agenda, Public Booking and ecommerce dependency graph.
- MySQL 8.4 migrations and dedicated test database isolation.
- Potential authorization/audit implications.
- No mandatory admin API is required for SPEC-003 by default.
- No frontend consumer is required for SPEC-003 by default.

Discovery itself did not implement migrations, models, endpoints, services or UI. Checkpoint A implementation is documented in `docs/reports/SPEC-003-CHECKPOINT-A-REPORT.md`.

## Implementation Boundaries

For the authorized Checkpoint B implementation:

- Work must occur on a dedicated implementation branch from updated `main`.
- Only approved Core domain rules, focused support classes, the weekly replacement action and tests are authorized.
- No migration, controller, Form Request, API route, Vue page or package installation is authorized in Checkpoint B.
- Backend remains the authority for domain rules and validation.
- Do not create Appointment Engine, CMS, ecommerce or public booking behavior as part of Core.
- Do not seed invented production business data.
- Do not add a generic repository or generic key/value settings system without approved evidence.
- Do not start SPEC-004 or any later SPEC.

## Implementation Checkpoints

Checkpoints A and B are authorized on the dedicated branch. Later checkpoints remain proposals and are not authorized:

### Checkpoint A - Approved domain model and persistence foundation

- Objective: implement the approved Core entities/relationships and technical persistence constraints.
- Scope: only the finalized in-scope concepts.
- Dependencies: Discovery, database model approval, privacy decisions and development authorization.
- Acceptance evidence: migrations/models/relationships and integrity tests against MySQL 8.4.
- Stop condition: stop if scope expands into appointment/CMS/ecommerce behavior.
- Status: `COMPLETED`.

### Checkpoint B - Domain actions and validation

- Objective: implement justified Actions/Services and backend invariants.
- Scope: normalization, validation, relationship integrity and approved settings behavior.
- Dependencies: Checkpoint A and approved business rules.
- Acceptance evidence: Feature/Unit tests, safe errors and authorization coverage.
- Stop condition: stop if a rule belongs to Appointment Engine or another deferred module.
- Status: `IN PROGRESS`.

### Checkpoint C - Integrity and lifecycle hardening

- Objective: verify constraints, FK delete behavior, active/inactive lifecycle, phone/privacy and race/integrity cases.
- Scope: domain/database integrity only; no API or UI.
- Dependencies: Checkpoints A-B and approved business rules.
- Acceptance evidence: constraint, relationship, normalization and privacy tests.
- Stop condition: stop before adding appointment, public booking, CMS or ecommerce behavior.

### Checkpoint D - Final tests and audit

- Objective: verify scope, security, privacy, quality, CI and documentation.
- Scope: all approved Core behavior only.
- Dependencies: prior checkpoints and human approval.
- Acceptance evidence: all AC/DoD items and remote CI pass.
- Stop condition: do not close SPEC-003 without explicit human acceptance.

## Scope-size Assessment

`APPROPRIATELY SIZED ONLY IF LIMITED TO SHARED CORE DATA AND RULES`.

It becomes too large if it includes appointment availability, public booking, admin agenda, CMS, ecommerce, notifications, reporting or broad admin UI. No SPEC-004 is created or proposed by this definition.

## Definition State

```text
SPEC-003: APPROVED FOR DEVELOPMENT / IN PROGRESS
Discovery: COMPLETED
Checkpoint A: COMPLETED
Checkpoint B: IN PROGRESS
Checkpoint C: NOT AUTHORIZED
Checkpoint D: NOT AUTHORIZED
```
