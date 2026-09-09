# SPEC-004 CHECKPOINT A REPORT

## 1. Initial Discovery HEAD
`1679483`.
## 2. Implementation branch
`feat/spec-004-appointment-engine`.
## 3. Documents/code reviewed
SPEC-004 Discovery, ADR-003, SPEC-003 closure, Core models/migrations/factories/tests and project testing conventions.
## 4. Checkpoint A scope
Appointment persistence, status enum, history persistence, models, relations, casts, factories and MySQL integrity tests only.
## 5. Scope exclusions
No schedules, time-off, availability, capacity calculation, locking workflows, Actions, API, frontend, payments or notifications.
## 6. AppointmentStatus enum
`App\Enums\AppointmentStatus`, backed string enum.
## 7. Final AppointmentStatus values
`confirmed`, `cancelled`, `completed`, `no_show` only.
## 8. Appointments migration
`2026_09_09_100000_create_appointments_table.php`.
## 9. Appointment schema
BIGINT id, three required Core FKs, UTC DATETIME start/end, positive duration snapshot, VARCHAR status and timestamps.
## 10. Appointment CHECK constraints
Start before end, duration positive and approved status vocabulary.
## 11. Appointment indexes
Professional/status/time and status/time indexes plus automatic FK indexes.
## 12. Appointment FK lifecycle
Customer, Service and Professional deletes are restrictive; no cascade to appointments.
## 13. Appointment model
`App\Models\Appointment`.
## 14. Appointment fillable
Explicit fields only; no unguarded model configuration.
## 15. Appointment casts
Datetime, integer and `AppointmentStatus` casts.
## 16. Appointment relationships
Customer, Service, Professional and AppointmentHistory relations.
## 17. Duration snapshot implementation
Persisted independent `duration_minutes`; Service edits do not alter historical appointments.
## 18. Price boundary
No price, currency or pricing snapshot.
## 19. Name snapshot boundary
No Service or Professional name snapshots.
## 20. Notes/source/public-ID boundary
No notes, duplicated PII, source, token or metadata.
## 21. schedule_version boundary
Absent and rejected for SPEC-004 V1.
## 22. History event representation
`App\Enums\AppointmentHistoryEventType`, backed string enum.
## 23. History event values
`created`, `status_changed`, `rescheduled`.
## 24. AppointmentHistories migration
`2026_09_09_100001_create_appointment_histories_table.php`.
## 25. AppointmentHistory schema
Appointment FK, event/status fields, old/new UTC temporal fields, old/new Professional references and created timestamp.
## 26. History constraints
Event types and non-null statuses use MySQL CHECK constraints.
## 27. History indexes
Appointment/created lookup and automatic Professional FK indexes.
## 28. History FK lifecycle
Appointment and Professional references are restrictive; historical rows cannot be cascade-deleted.
## 29. AppointmentHistory model
Append-only `App\Models\AppointmentHistory` with `UPDATED_AT = null`.
## 30. History casts
Event/status enums, nullable datetimes and integer references.
## 31. History relations
Appointment, old Professional and new Professional.
## 32. Append-only strategy
No updated timestamp, observers, hooks or automatic history generation.
## 33. Core inverse relations
No Core model behavior changes were needed.
## 34. Appointment factory
`database/factories/AppointmentFactory.php`.
## 35. AppointmentHistory factory
`database/factories/AppointmentHistoryFactory.php`.
## 36. Seeders
None created or modified.
## 37. Appointment persistence tests
All status values, relations, schema and valid persistence covered.
## 38. Status integrity tests
Enum round-trip and direct unknown-status rejection.
## 39. Temporal constraint tests
Equal/reversed times rejected; ordered times accepted.
## 40. Duration constraint tests
Zero/invalid values rejected; positive values accepted.
## 41. FK restriction tests
Referenced Customer, Service and Professional deletion rejected.
## 42. History persistence tests
Created and rescheduled events persist with nullable fields.
## 43. History event tests
Unknown event types rejected.
## 44. History status tests
Nullable statuses accepted; unknown non-null statuses rejected.
## 45. History temporal tests
Old/new UTC values round-trip without mutating Appointment.
## 46. History Professional tests
Old/new Professional relations persist and load.
## 47. Appointment hard-delete/history test
Appointments with history cannot be deleted.
## 48. MySQL metadata verification
CHECK, FK and index metadata verified on MySQL 8.4.
## 49. Final SPEC-004 table set after A
`appointments`, `appointment_histories` only.
## 50. Dependencies
No Composer/npm dependencies added.
## 51. API changes
None.
## 52. Frontend changes
None.
## 53. Route changes
None.
## 54. Test DB
`agenda_estetica_test`.
## 55. MySQL version
`8.4.11`.
## 56. Fresh migration
PASS.
## 57. Rollback
PASS.
## 58. Re-migration
PASS.
## 59. Backend test result
PASS: 66 tests, 217 assertions.
## 60. Frontend test result
PASS: 10 files, 24 tests.
## 61. Pint
PASS.
## 62. PHPStan
PASS.
## 63. Composer validate
PASS.
## 64. Composer audit
PASS.
## 65. Frontend gates
Lint, typecheck, Vitest, build and npm audit: PASS.
## 66. Health regression
PASS: health returns 200 JSON.
## 67. C.1 API regression
PASS: auth failures are JSON 401 and unknown API routes are JSON 404.
## 68. Route audit
PASS: no Appointment/API routes.
## 69. Security/privacy audit
No duplicated PII, hidden lifecycle behavior or public exposure.
## 70. Scope audit
No B/C functionality, schedules, time-off, availability, capacity or operations.
## 71. Documentation changes
SPEC/roadmap status and this report were updated.
## 72. Report path
`docs/reports/SPEC-004-CHECKPOINT-A-REPORT.md`.
## 73. Commits
Pending final review.
## 74. Commit hashes
Pending.
## 75. Push result
Pending.
## 76. Remote CI workflow
Pending push.
## 77. Remote CI run ID
Pending push.
## 78. Remote backend result
Pending push.
## 79. Remote frontend result
Pending push.
## 80. Working tree
Pending final staging review.
## 81. Local/remote synchronization
Implementation branch began at Discovery HEAD `1679483`; final synchronization pending.
## 82. SPEC-004 status
`APPROVED FOR DEVELOPMENT / IN PROGRESS`.
## 83. Checkpoint A status
`COMPLETED`.
## 84. Checkpoint B status
`NOT AUTHORIZED`.
## 85. SPEC-005 status
`NOT STARTED`.
## 86. Blockers
None for Checkpoint A.
## 87. Recommended next action
Submit Checkpoint A for human review and stop. Do not begin Checkpoint B.
