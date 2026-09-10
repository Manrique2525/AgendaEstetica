# SPEC-004 CHECKPOINT B REPORT

## 1. Initial state

- Branch: `feat/spec-004-appointment-engine`.
- Initial HEAD: `2700eee`.
- Discovery base: `1679483`.
- Working tree: clean.

## 2. Scope

Implemented only recurring Professional schedules, Professional TimeOff, focused overlap validation and atomic schedule replacement. Appointment availability, capacity, booking, mutations and Checkpoint C remain out.

## 3. ProfessionalSchedule persistence

Schema:

```text
id
professional_id
weekday
starts_at TIME
ends_at TIME
created_at
updated_at
```

No `interval_order`, active flag, date, timezone, JSON or SoftDeletes.

Constraints: weekday `1-7`, `starts_at < ends_at`, exact UNIQUE tuple `(professional_id, weekday, starts_at, ends_at)`, Professional FK `CASCADE`.

## 4. ProfessionalSchedule domain behavior

- Multiple intervals/day: supported.
- Adjacent intervals: allowed.
- Same-day overlap: rejected by focused validator.
- Different weekdays: allowed.
- Different Professionals: allowed.
- Closed day: zero rows.
- Overnight rows: rejected.

## 5. ReplaceProfessionalSchedule

`App\Actions\ReplaceProfessionalSchedule` validates the complete proposal before writes, locks only the target Professional with `FOR UPDATE`, deletes only that Professional's rows, inserts the replacement atomically and supports empty schedules.

## 6. ProfessionalTimeOff persistence

Schema:

```text
id
professional_id
starts_at DATETIME UTC
ends_at DATETIME UTC
created_at
updated_at
```

Constraints: `starts_at < ends_at`, exact duplicate UNIQUE tuple and Professional FK `CASCADE`.

## 7. ProfessionalTimeOff domain behavior

`ProfessionalTimeOffOverlapValidator` rejects same-Professional partial, containment, same-start, same-end and exact overlaps. Adjacent intervals and equal intervals for different Professionals are allowed.

## 8. Models and factories

- `App\Models\ProfessionalSchedule`.
- `App\Models\ProfessionalTimeOff`.
- `App\Support\ProfessionalScheduleValidator`.
- `App\Support\ProfessionalTimeOffOverlapValidator`.
- `Professional` inverse `schedules()` and `timeOff()` relations.
- Factories for both models.

## 9. Tests

- Schedule persistence and constraints.
- Schedule overlap/adjacency/different-day/different-Professional cases.
- Atomic replacement and old-schedule preservation on invalid input.
- Cross-Professional replacement isolation.
- TimeOff persistence, constraints, duplicate/FK behavior and overlap validator.

## 10. Out of scope audit

Not implemented:

- Availability or capacity calculations.
- Appointment overlap queries.
- Appointment create/reschedule/cancel/status Actions.
- Appointment locking protocol.
- BusinessProfile locking.
- Slot generation.
- API, frontend, payments, notifications, WhatsApp or special hours.
- Checkpoint C, D, E or SPEC-005.

## 11. Database lifecycle

- Fresh migration: PASS.
- Rollback: PASS.
- Re-migration: PASS.
- Test DB: `agenda_estetica_test`.
- MySQL: `8.4.11`.

## 12. Quality gates

- Backend: 74 tests, 264 assertions, PASS.
- Frontend: 10 files, 24 tests, PASS.
- Pint: PASS.
- PHPStan: PASS.
- Composer validate/audit: PASS.
- ESLint/typecheck/build/npm audit: PASS.

## 13. Routes and dependencies

- No routes added.
- No Composer dependencies added.
- No npm dependencies added.
- No seeders added.

## 14. Checkpoint status

- SPEC-004: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Checkpoint A: `COMPLETED / APPROVED`.
- Checkpoint B: `COMPLETED`.
- Checkpoint C: `NOT AUTHORIZED`.
- SPEC-005: `NOT STARTED`.

## 15. Report path

`docs/reports/SPEC-004-CHECKPOINT-B-REPORT.md`.

## 16. Commits

- `f935085 feat: add SPEC-004 professional schedule foundation`.
- `dc6aff2 docs: report SPEC-004 checkpoint B`.

## 17. Push result

PASS: `git push origin feat/spec-004-appointment-engine`.

## 18. Remote CI

- Workflow: `Quality`.
- Run: `34371280731`.
- Backend: PASS.
- Frontend: PASS.

## 19. Final synchronization

The implementation branch is synchronized with its remote and the working tree is clean.

## 20. Final checkpoint state

- SPEC-004: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Checkpoint A: `COMPLETED / APPROVED`.
- Checkpoint B: `COMPLETED`.
- Checkpoint C: `NOT AUTHORIZED`.
- SPEC-005: `NOT STARTED`.

## 21. Recommended next action

Submit Checkpoint B for human review. Do not start Checkpoint C.
