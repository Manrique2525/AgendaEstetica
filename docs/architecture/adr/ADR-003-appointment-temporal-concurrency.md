# ADR-003 - Appointment Temporal and Concurrency Direction

## Status

`ACCEPTED`

## Context

SPEC-004 Technical Discovery requires a stable temporal and concurrency direction before appointment migrations or Actions are implemented. SPEC-003 stores recurring business rules as local `TIME` values and keeps the technical application timezone at UTC. Appointment Engine introduces concrete dated events, professional overlap and global capacity races.

## Decision

1. Persist concrete appointment and time-off instants as UTC MySQL `DATETIME` values. Do not use MySQL `TIMESTAMP` for domain instants because session timezone conversion changes returned values.
2. Keep recurring BusinessHours and ProfessionalSchedule intervals as business-local `TIME` values interpreted through `BusinessProfile.timezone`.
3. Reject nonexistent local DST times and require explicit disambiguation for ambiguous local folds at a future input boundary.
4. Protect capacity-affecting mutations with one transaction and a deterministic lock order: singleton BusinessProfile, affected Professional rows ascending by ID, then existing Appointment row.
5. Re-read availability after locks and before writing appointment/history records.
6. Do not add `schedule_version` in SPEC-004 V1; lock/revalidation is the accepted stale-write strategy.

## Rationale

The project is a single small salon, so serializing capacity-affecting writes on the singleton profile is a proportionate correctness tradeoff. Professional row locks prevent same-resource overlap races. UTC `DATETIME` values avoid implicit session conversion while recurring schedules remain human/business-local.

## Consequences

- Availability inputs require an explicit business-local-to-UTC conversion boundary.
- MySQL integration tests must use independent connections for race scenarios.
- Lock ordering and transaction behavior become part of the Appointment Engine contract.
- Future public/admin consumers must not bypass the authoritative mutation path.

## Alternatives Rejected for Now

- MySQL `TIMESTAMP`: rejected for predictable domain querying because session timezone conversion is implicit.
- Unprotected check-then-insert: rejected because it cannot prevent overlap/capacity races.
- Distributed locks/Redis: rejected as disproportionate and outside approved architecture.
- Premature `schedule_version`: rejected until a concrete stale-write problem remains after locking/revalidation.
