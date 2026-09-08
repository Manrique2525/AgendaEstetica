# SPEC-003 Implementation Report

## Executive Summary

SPEC-003 implements the approved single-business Core persistence and domain-invariant foundation through Checkpoints A-C. Checkpoint D completes the final technical audit without adding product functionality. The result is ready for human acceptance, but is not closed and has not been merged.

## Scope

Implemented:

- Typed singleton `BusinessProfile` and recurring `BusinessHour` persistence.
- `ServiceCategory`, `Service`, `Professional`, `ProfessionalService` and `Customer` persistence.
- Backed string `ServicePricingType` enum.
- MySQL CHECK, UNIQUE and FK integrity constraints.
- Explicit customer phone normalizer.
- Native IANA timezone validator.
- Atomic, locked weekly BusinessHours replacement action.
- Integrity, lifecycle, privacy and regression tests.

Not implemented:

- API, controllers, requests, resources or routes.
- Vue, admin UI or public UI.
- Appointment Engine, availability, booking, capacity enforcement or appointment state.
- Admin Agenda, CMS, ecommerce, notifications, WhatsApp or payments.
- Production data, seeders, customer deduplication or customer accounts.

## Architecture

- Existing Laravel modular monolith preserved.
- Models remain under `App\Models`.
- Explicit reusable rules live under `App\Support`.
- The multi-row schedule operation lives under `App\Actions`.
- No repositories, CQRS, event bus, generic managers or new dependencies.

## Checkpoint Summary

| Checkpoint | Result | Evidence |
| --- | --- | --- |
| A - persistence foundation | COMPLETED | migrations, models, factories and MySQL tests |
| B - domain invariants | COMPLETED | phone/timezone rules and schedule replacement tests |
| C - integrity/lifecycle hardening | COMPLETED | FK, lifecycle, privacy, mass-assignment and index audits |
| D - final audit | COMPLETED | final gates, AC/DoD audit, reports and remote CI |

## Table Model

The final Core table set is exactly:

```text
business_profiles
business_hours
service_categories
services
professionals
professional_service
customers
```

There is no multitenancy, generic settings table or additional Core table.

## Domain Invariants

- `users`, `professionals` and `customers` are separate concepts.
- `business_profiles.singleton_key = 1` is enforced by CHECK and UNIQUE constraints.
- Capacity is positive configuration only.
- Weekly hours use ISO weekdays and strict opening/closing order.
- Overlap is rejected by `ReplaceBusinessHours`; adjacency is accepted.
- Category, service and professional active states do not cascade into other active states or compatibility rows.
- Service pricing is constrained by enum values and MySQL checks.
- Professional-service pairs are unique and FK-protected.
- Customer normalized phones are indexed but not unique.

## Security and Privacy

- Explicit fillable attributes are used; `BusinessProfile.singleton_key` is excluded.
- No authentication model changes were introduced.
- Customer data is minimal and has no public representation.
- Phone exceptions do not include submitted PII.
- No logging, external API or dependency was added for phone normalization.
- Static migration SQL contains no user interpolation.

## Business Data

The following remain pending and are not seeded:

- Official IANA business timezone.
- Initial BusinessProfile row.
- Real professionals and compatibility assignments.
- Real service categories, services, durations and prices.
- Special hours, exceptions and future appointment policies.

## Test Evidence

```text
Backend: 57 tests, 178 assertions, PASS
Frontend: 10 files, 24 tests, PASS
MySQL: 8.4.11
Database: agenda_estetica_test
```

Migration lifecycle:

- Fresh migration: PASS.
- Rollback: PASS.
- Re-migration: PASS.
- Pest after re-migration: PASS.

## Acceptance Criteria Matrix

| AC | Result | Evidence |
| --- | --- | --- |
| AC-01 approved Core scope without deferred modules | PASS | Scope audit and file audit |
| AC-02 users/professionals/customers remain separate | PASS | Models, migrations and auth regression tests |
| AC-03 approved conceptual model implemented | PASS | Seven-table model and relationship tests |
| AC-04 business settings separated from `.env` | PASS | Typed `business_profiles` persistence |
| AC-05 IANA timezone validation without real value | PASS | `IanaTimezoneValidator` unit tests |
| AC-06 duration, active, category and compatibility | PASS | Models, constraints and persistence tests |
| AC-07 pricing behavior approved and unseeded | PASS | Enum, CHECK constraints and round-trip tests |
| AC-08 professional-service uniqueness/integrity | PASS | Composite PK, FK and pivot tests |
| AC-09 minimal customer identity without account | PASS | Schema/privacy audit and tests |
| AC-10 capacity separated from enforcement | PASS | Positive CHECK; no booking logic |
| AC-11 deposit policy deferred | PASS | No deposit implementation or data |
| AC-12 validation, auth boundaries, mass assignment and JSON safety | PASS | Actions/support tests, fillable audit and Foundation API tests |
| AC-13 constraints, indexes, timestamps and soft-delete decisions | PASS | MySQL metadata and lifecycle matrix |
| AC-14 dedicated MySQL test database | PASS | `agenda_estetica_test`, MySQL 8.4.11 |
| AC-15 Foundation/frontend gates remain green | PASS | Backend and frontend gates |
| AC-16 no generic repository/dependency/business seed | PASS | Scope, dependency and seeder audits |
| AC-17 documentation, reports and remote CI | PASS | A/B/C/D reports and Quality CI |
| AC-18 human acceptance before close | PASS WITH HUMAN ACCEPTANCE PENDING | Technical readiness complete; user acceptance not yet recorded |

## Definition of Done Matrix

| Item | Result | Evidence |
| --- | --- | --- |
| Approved scope only | PASS | Scope audit |
| ACs satisfied or exception documented | PASS | Matrix above; AC-18 pending human acceptance |
| Domain model/invariants documented | PASS | SPEC and reports |
| MySQL integrity tested | PASS | Direct DB tests and metadata |
| Auth/API contracts intact | PASS | Foundation regressions |
| Existing auth/queue/storage/frontend intact | PASS | Regression gates |
| PHP/npm quality gates | PASS | Local and remote Quality workflow |
| Security/privacy/concurrency reviewed | PASS | D audit and C report |
| No deferred modules/data | PASS | Scope audit |
| Reports complete | PASS | Discovery, A, B, C and D reports |
| Remote CI green | PASS | Final Quality run |
| Working tree clean | PASS | Final Git audit |
| Human acceptance recorded | PENDING | Requires user decision |

## Final Technical Status

SPEC-003 is technically ready for human acceptance. It remains open by policy and is not merged. Checkpoint D does not authorize main-branch merge or SPEC-004.
