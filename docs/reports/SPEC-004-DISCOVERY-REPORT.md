# SPEC-004 TECHNICAL DISCOVERY REPORT

## 1. Repository initial state

- Repository: `AgendaEstetica`.
- Initial base: `fa9ff81`.
- Main/origin main: `22f5f09`.
- Definition branch: `docs/spec-004-definition`.
- Working tree before Discovery: clean.

## 2. Discovery branch

`docs/spec-004-discovery`, created from `fa9ff81`.

## 3. Initial HEAD

`fa9ff81 docs: define SPEC-004 appointment engine`.

## 4. Documents/code reviewed

Reviewed `AGENTS.md`, context, architecture and ADRs, roadmap, domain rules, testing plan, SPEC-001/002/003, SPEC-003 reports, SPEC-004 Definition, all Core models, enum, Action, support classes, migrations, factories and tests.

## 5. Existing Core contract

SPEC-003 provides `BusinessProfile`, recurring `BusinessHour`, `ServiceCategory`, `Service`, `Professional`, `ProfessionalService`, `Customer`, pricing enum, phone normalizer, timezone validator and transactional BusinessHours replacement.

## 6. Existing DB conventions

Laravel anonymous migrations, BIGINT identities, MySQL 8.4, `utf8mb4`, timestamps, explicit FKs, CHECK/UNIQUE constraints and dedicated `agenda_estetica_test` persistence.

## 7. Proposed final table set

Recommended new conceptual tables:

```text
appointments
appointment_histories
professional_schedules
professional_time_off
```

No business special-hours table is recommended for SPEC-004 V1.

## 8. Proposed final model/enum set

- `Appointment`.
- `AppointmentHistory`.
- `ProfessionalSchedule`.
- `ProfessionalTimeOff`.
- `AppointmentStatus` PHP backed string enum.
- Focused domain Actions/services for availability and mutations.

## 9. Appointment exact schema recommendation

Recommended minimum fields:

```text
id
customer_id
service_id
professional_id
starts_at DATETIME UTC
ends_at DATETIME UTC
status string
created_at
updated_at
```

No source, notes, names, price or `schedule_version` by default.

## 10. Appointment indexes

Discovery recommends indexes supporting professional/status/temporal overlap lookup, capacity status/temporal lookup and FK access. Exact composite order requires schema review before implementation.

## 11. Appointment constraints

Required candidates: FKs, `starts_at < ends_at`, approved status values as strings, positive duration snapshot and indexes for overlap/capacity queries. Exact SQL constraints require implementation approval.

## 12. Appointment FK lifecycle

Historical appointments should not be destroyed by deleting Customer, Service or Professional. Discovery recommends restrictive FKs or an explicitly approved historical-reference strategy; no cascade deletion of appointments.

## 13. Time persistence recommendation

Persist concrete dated events as UTC MySQL `DATETIME`. Convert business-local input using `BusinessProfile.timezone` at a future boundary. Keep recurring schedules as local `TIME` values.

## 14. DATETIME/TIMESTAMP decision

Recommend `DATETIME`, not `TIMESTAMP`. A live MySQL experiment showed `TIMESTAMP` changed from `10:00` to `12:00` when the session timezone changed to `+02:00`, while `DATETIME` remained `10:00`. UTC normalization is application-owned and predictable.

## 15. Business timezone conversion model

Recurring local rules use `BusinessProfile.timezone`; concrete instants are normalized to UTC. The exact business timezone remains business-data pending.

## 16. DST gap behavior

Recommended: reject nonexistent local times at the input boundary. Do not silently normalize a spring-forward gap.

## 17. DST fold behavior

Recommended: reject ambiguous local times unless a future consumer supplies an explicit offset/disambiguation. Do not silently choose one occurrence.

## 18. Duration historical-integrity decision

Historical appointment timing must not change when Service duration changes. Discovery recommends storing `starts_at`, `ends_at` and a positive `duration_minutes` snapshot.

## 19. Duration snapshot decision

`duration_minutes` snapshot: `RECOMMENDED / DEVELOPMENT-APPROVAL BLOCKER`.

## 20. Appointment price decision

Appointment price is `DISCOVERY RESOLVED AS DEFERRED FROM ENGINE BY DEFAULT`. It returns only if a concrete Engine invariant requires an agreed amount; it must not become an order/sales record.

## 21. Service-name snapshot decision

`OUT OF SPEC-004 V1`. Prefer FK/current catalog; a future legal/accounting consumer may add a snapshot.

## 22. Professional-name snapshot decision

`OUT OF SPEC-004 V1`. Prefer FK/current resource display; a future legal/accounting consumer may add a snapshot.

## 23. Final Appointment statuses

Final V1 operational baseline:

```text
confirmed
cancelled
completed
no_show
```

`REQUEST_RECEIVED`, `PENDING_APPROVAL`, `APPROVED`, `REJECTED`, `DEPOSIT_PENDING` and `RESCHEDULED` are not V1 persistent statuses. Request/approval/rejection vocabulary remains future consumer workflow.

## 24. Status semantics matrix

| Status | Meaning | Terminal | Blocking |
| --- | --- | --- | --- |
| confirmed | approved/accepted appointment | No | Yes |
| cancelled | cancelled historical record | Yes | No |
| completed | completed historical record | Yes | No |
| no_show | no-show historical record | Yes | No |

## 25. Transition graph

Recommended graph:

```text
confirmed -> cancelled, completed, no_show
cancelled -> terminal
completed -> terminal
no_show   -> terminal
```

Creation produces `confirmed`; reschedule preserves `confirmed` and writes history.

## 26. Invalid-transition examples

Reject transitions such as `cancelled -> completed`, `completed -> confirmed` and `no_show -> confirmed`. Terminal states have no outgoing transitions.

## 27. Professional-blocking statuses

`confirmed` blocks. There is no pending V1 status.

## 28. Capacity-counting statuses

`confirmed` counts; cancelled, completed and no-show do not.

## 29. AppointmentHistory recommendation

Focused `appointment_histories` is `IN` for creation, status changes, reschedules, cancellations, completion and no-show facts. Generic audit logs remain out.

## 30. AppointmentHistory schema

Recommended fields:

```text
id
appointment_id
event_type
from_status nullable
to_status nullable
previous_starts_at nullable UTC
previous_ends_at nullable UTC
new_starts_at nullable UTC
new_ends_at nullable UTC
previous_professional_id nullable
new_professional_id nullable
created_at
```

Exact representation requires implementation approval.

## 31. Reschedule history strategy

Reschedule stores old/new temporal boundaries and professional references when changed. It does not introduce `RESCHEDULED` as an operational status.

## 32. ProfessionalSchedule schema

Recommended recurring table:

```text
id
professional_id
weekday 1..7
starts_at TIME
ends_at TIME
timestamps
```

## 33. ProfessionalSchedule constraints

Require weekday `1-7`, `starts_at < ends_at`, and `UNIQUE(professional_id, weekday, starts_at, ends_at)`. Same-day overlap is domain validation. Adjacent intervals are allowed. No overnight row is allowed. Ordering is naturally `ORDER BY starts_at`; no persisted manual order is approved.

## 34. ProfessionalSchedule replacement strategy

Recommend an atomic replacement operation analogous to `ReplaceBusinessHours`: validate first, lock Professional, delete/recreate in one transaction. Implementation is not authorized.

## 35. ProfessionalTimeOff schema

Recommended minimal table:

```text
id
professional_id
starts_at DATETIME UTC
ends_at DATETIME UTC
timestamps
```

No HR fields, reason, approval, attachment or leave type.

## 36. ProfessionalTimeOff constraints

Require `starts_at < ends_at`, FK integrity and temporal lookup indexes. Overlap policy requires development approval.

## 37. TimeOff overlap policy

Recommend rejecting overlapping intervals for the same Professional through domain validation; exact DB protection is not straightforward for arbitrary ranges and needs implementation design.

## 38. Business-special-hours final boundary

`DEFERRED`. SPEC-004 V1 uses existing BusinessHours as the global recurring boundary and does not add special-hours tables.

## 39. Availability contract

`Validate requested appointment interval`: `IN`.

Candidate slot generation: `DEFERRED TO CONSUMER / FUTURE SCOPE` until Admin Agenda/Public Booking needs it.

## 40. Availability inputs

Minimum conceptual inputs: specific BusinessProfile context, specific Professional, Service and requested business-local start/end or start plus duration.

## 41. Availability validation order

BusinessHours, ProfessionalSchedule, ProfessionalTimeOff, active states, ProfessionalService compatibility, duration fit, professional overlap and global capacity.

## 42. Slot-generation decision

Deferred. The Engine validates a requested interval; it does not generate calendars before a consumer exists.

## 43. Slot-granularity status

`BUSINESS-RULE / DISCOVERY REQUIRED`, non-blocking while slot generation is deferred.

## 44. Buffer-time status

`OUT`; no approved cleanup/buffer requirement exists.

## 45. ServiceCategory.active rule

`RESOLVED`: inactive category blocks new appointments without mutating Service or compatibility records.

## 46. Service.active rule

Inactive Service blocks new appointments but does not modify historical appointments or compatibility rows.

## 47. Professional.active rule

Inactive Professional blocks new appointments but does not modify historical appointments or compatibility rows.

## 48. ProfessionalService rule

Compatibility remains authoritative; appointments reference Professional directly and do not depend on the pivot remaining unchanged for historical reads.

## 49. Professional overlap rule

Use `startA < endB AND startB < endA`. Adjacent intervals are valid.

## 50. Adjacency rule

`10:00-11:00` and `11:00-12:00` do not overlap.

## 51. Overlap query/index strategy

Candidate query: same Professional, blocking status, `starts_at < candidate_end` and `ends_at > candidate_start`; then validate intervals under transaction. Index order requires implementation review.

## 52. Global-capacity algorithm

Query all blocking appointments overlapping the candidate interval, add candidate boundaries, sort events and perform a half-open interval sweep. Reject if peak concurrency exceeds configured capacity.

## 53. Capacity overlap example

For capacity `2`, A `10:00-11:00`, B `10:30-11:30`, candidate `10:45-11:15`, peak concurrency is 3 and the candidate must be rejected.

## 54. Capacity query/analysis strategy

Use overlap filtering in MySQL and interval/event analysis in the transaction. Same-start counting is insufficient.

## 55. Same-Professional race analysis

Lock the singleton BusinessProfile, then the target Professional, re-read blocking appointments and insert only after revalidation. At most one conflicting write succeeds.

## 56. Global-capacity race analysis

Locking the singleton BusinessProfile serializes capacity-affecting writes for the single small salon; each transaction recomputes peak concurrency before commit.

## 57. Recommended lock architecture

`BusinessProfile -> Professional(s) ascending ID -> Appointment if existing -> revalidate -> write/history`.

## 58. Lock order

Always lock BusinessProfile first, then affected Professional IDs in ascending order, then existing Appointment rows. This avoids inconsistent multi-professional ordering.

## 59. Reschedule lock strategy

Lock BusinessProfile, all old/new Professionals sorted by ID, then Appointment; re-read current state and availability before updating.

## 60. State-transition lock strategy

For transitions changing blocking semantics, use the same capacity/professional lock protocol, then lock/re-read the Appointment and write history in the transaction.

## 61. Cancellation/confirmation lock impact

Creation is confirmed in V1. Confirmation is not a separate pending transition. Cancellation releases occupancy and uses the same lock protocol when blocking state changes.

## 62. Transaction boundaries

Create, reschedule, cancellation when blocking state changes, completion/no-show transitions when availability changes, status transitions and schedule replacement require transactions where specified by final semantics.

## 63. Read-only availability vs authoritative write validation

Availability previews may become stale. Every mutation must lock, re-read, revalidate and commit atomically.

## 64. `schedule_version` final decision

`REJECTED FOR SPEC-004 V1`. Locking, authoritative revalidation and deterministic transaction ordering are the selected stale-write strategy.

## 65. Idempotency boundary

HTTP request-token idempotency is deferred to a future consumer. Database race safety is not deferred.

## 66. Appointment-source boundary

Deferred to Admin/Public/WhatsApp consumer specifications.

## 67. Public-identifier boundary

Deferred; internal BIGINT remains baseline.

## 68. Appointment deletion/lifecycle

Appointments are retained stateful records with no normal hard-delete workflow. Appointment references to Customer, Service and Professional use restrictive historical lifecycle behavior.

## 69. Customer historical FK strategy

`RESOLVED`: restrictive historical reference behavior; deleting a referenced Customer must not destroy appointments.

## 70. Service historical FK strategy

`RESOLVED`: restrictive historical reference behavior; inactive/editable Service data must not rewrite existing appointments.

## 71. Professional historical FK strategy

`RESOLVED`: restrictive historical reference behavior; deleting a Professional must not destroy appointments.

## 72. ProfessionalService historical implication

Existing compatibility may be removed for future assignments, but historical Appointment reads use their direct Professional/Service references.

## 73. Database-constraint matrix

| Concern | Protection |
| --- | --- |
| Appointment references | FK, restrictive historical policy |
| Status vocabulary | string column plus PHP enum/domain validation |
| Time order | CHECK |
| Duration snapshot | CHECK positive |
| Professional schedule | CHECK + UNIQUE + domain overlap |
| Time off | CHECK + FK + domain overlap |
| Appointment overlap | transaction/locking/domain query |
| Capacity | transaction/locking/interval analysis |

## 74. Index matrix

| Concern | Recommended support |
| --- | --- |
| Professional overlap | Professional/status/starts_at/ends_at access path |
| Capacity | Status/starts_at/ends_at access path |
| Professional schedule | Professional/weekday |
| Time off | Professional/starts_at/ends_at |
| History | Appointment/created_at |

Exact index order requires implementation review against MySQL plans.

## 75. MySQL test environment

Verified: MySQL `8.4.11`, `agenda_estetica_test`, `127.0.0.1:3307`, `APP_ENV=testing`.

## 76. Safe DB experiments performed

- Core metadata, constraints and indexes inspected.
- Temporary `DATETIME`/`TIMESTAMP` table used and automatically discarded.
- Temporary lock fixture created and cleaned in test DB.

## 77. Two-connection locking experiment

Two PDO connections targeted the singleton BusinessProfile row. Connection A held `SELECT ... FOR UPDATE`; connection B attempted the same lock.

## 78. Experiment result

Connection B was blocked for approximately `1.01s` until A committed. Fixtures were cleaned; `business_profiles` and `business_hours` counts returned to zero.

## 79. Contention/scalability assessment

Singleton serialization is acceptable for one small salon and favors correctness over speculative throughput. It should be revisited only with real load evidence.

## 80. Deadlock analysis

Primary risk is rescheduling between professionals. Sorted Professional IDs and a fixed BusinessProfile-first order are recommended. No distributed lock is required.

## 81. Retry recommendation

Use Laravel/MySQL transaction retry only if implementation demonstrates transient deadlocks; do not add a generic retry wrapper during Discovery.

## 82. Security assessment

Backend authority, explicit validation, historical protection, no unguarded API and no customer authentication changes are required. No implementation code was added.

## 83. Privacy assessment

Minimize customer-linked appointment fields. Notes are not recommended without a concrete need. No public exposure or marketing data is part of the Engine.

## 84. Notes-field decision

`OUT BY DEFAULT`; reconsider only with a concrete operational use case and privacy decision.

## 85. API boundary

`NONE` for SPEC-004 Engine.

## 86. Frontend boundary

`NONE` for SPEC-004 Engine.

## 87. Admin/Public consumer boundary

Admin Agenda and Public Booking remain later consumers; no HTTP or UI contract is defined here.

## 88. Automated concurrency-test strategy

Use independent MySQL connections/transactions in Pest integration tests for overlap, capacity, reschedule and state races.

## 89. Test isolation strategy

Use the dedicated MySQL test database, unique fixtures, explicit cleanup and no long-lived transaction wrapper around the competing connections. Ensure locks release in `finally`/rollback paths.

## 90. DST test strategy

Use technical DST timezones to test nonexistent and ambiguous local times. Tests must assert rejection/disambiguation behavior without selecting Yaris's production timezone.

## 91. Migration lifecycle strategy

Future implementation must validate fresh migration, rollback, re-migration and full Pest against `agenda_estetica_test` on MySQL 8.4.

## 92. ADR assessment

ADR-003 is `ACCEPTED` and records the UTC `DATETIME` event model, local recurring schedules, DST rejection/disambiguation, deterministic lock ordering and `schedule_version` rejection. It does not authorize implementation by itself.

## 93. Scope-size assessment

`LARGE BUT APPROPRIATELY BOUNDED` when limited to appointments, schedules, availability, capacity, lifecycle, transactions and concurrency. Consumer/integration scope would make it too large.

## 94. Final recommended Actions/domain operations

- `CheckAppointmentAvailability`.
- `CreateAppointment`.
- `RescheduleAppointment`.
- `CancelAppointment`.
- `TransitionAppointmentStatus`.
- `ReplaceProfessionalSchedule`.

No classes are created by Discovery.

## 95. Final proposed checkpoints

- A: persistence/status/history.
- B: professional schedules/time-off.
- C: availability/overlap/capacity.
- D: transactional operations.
- E: concurrency hardening.
- F: final audit.

## 96. Checkpoint-E concurrency clarification

Checkpoint E is stress/race/deadlock validation and hardening, not permission to implement unsafe operations first.

## 97. Technical blockers resolved

Resolved by Discovery: temporal storage recommendation, final V1 status baseline, professional schedule scope, time-off scope, special-hours deferral, specific-professional scope, lock anchor/order, capacity algorithm direction and `schedule_version` rejection.

## 98. Remaining business-rule questions

- Cancellation timing/fee policy.
- No-show operational policy.
- Final category-inactive interpretation.
- Exact local-time input policy at consumers.

## 99. Business-data pending

Official timezone, real profile, professionals, services, prices, schedules and time-off remain pending.

## 100. Deferred decisions

Public/admin HTTP, UI, notifications, payments/deposits, business special hours, consumer idempotency/source/public IDs, customer auth/deduplication and generic audit/retention.

## 101. SPEC changes

`SPEC-004` updated with resolved Discovery recommendations, final blockers and `READY FOR DEVELOPMENT APPROVAL` status.

## 102. Roadmap changes

`SPEC-004` updated from `READY FOR DISCOVERY` to `READY FOR DEVELOPMENT APPROVAL`.

## 103. Discovery report path

`docs/reports/SPEC-004-DISCOVERY-REPORT.md`.

## 104. Application-code changes

None.

## 105. Backend regression result

Baseline regression gates remain available from SPEC-003. No application code changed during Discovery.

## 106. Frontend regression result

No frontend code changed. No new frontend gate was required for documentation-only Discovery.

## 107. Commit(s)

Two documentation commits were created for Definition and final decision corrections.

## 108. Commit hashes

- `1b3f67b docs: complete SPEC-004 technical discovery`.
- `e7db7c5 docs: finalize SPEC-004 discovery decisions`.

## 109. Push result

PASS: `git push -u origin docs/spec-004-discovery`.

## 110. Remote CI result

No GitHub Actions run was triggered for the documentation-only push.

## 111. Working tree

Clean before this report synchronization commit.

## 112. Local/remote synchronization

Discovery branch began at `fa9ff81`; it is synchronized through `e7db7c5` before this report-only update.

## 113. Previous SPEC status

SPEC-003: `CLOSED`.

## 114. New SPEC status

SPEC-004: `READY FOR DEVELOPMENT APPROVAL`.

## 115. Implementation status

`NOT AUTHORIZED`.

## 116. Checkpoint A status

`NOT AUTHORIZED`.

## 117. SPEC-005 status

`NOT STARTED`.

## 118. Blockers

Development-approval blockers: `NONE` from Technical Discovery. Human approval remains required before implementation; business timing/no-show policy and real business data are non-blocking pending items.

## 119. Recommended next action

Review SPEC-004 Technical Discovery and ADR-003 draft. Request explicit authorization for development approval before creating Checkpoint A or application code.
