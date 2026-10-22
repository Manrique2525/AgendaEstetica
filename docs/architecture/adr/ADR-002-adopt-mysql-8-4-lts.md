# ADR-002 - Adopt MySQL 8.4 LTS

## Title

Adopt MySQL 8.4 LTS as the project database target.

## Status

`ACCEPTED`

## Date

2026-09-08

## Context

The project architecture previously referred to MySQL 8 generically. During SPEC-001 Checkpoint A, the local machine had an existing MySQL 9.2 installation but no usable MySQL 8 server connection. A new database server was therefore required before Foundation migrations could be validated.

MySQL 8.0 is no longer the appropriate new-installation target because its support lifecycle has ended. MySQL 8.4 is the LTS line within MySQL 8 and is compatible with Laravel 13 and PDO MySQL.

## Options Considered

### A. MySQL 8.0

Rejected because it is no longer an appropriate supported target for a new project.

### B. MySQL 8.4 LTS

Provides the LTS support line, stable behavior and compatibility with the approved Laravel/PHP stack.

### C. MySQL Innovation/current

Rejected because an Innovation release does not provide the stability and support horizon desired for the project database baseline.

## Decision

Choose option B: MySQL 8.4 LTS.

Production, CI and local Foundation validation must target MySQL 8.4 LTS. The application continues to use Laravel's MySQL driver and PDO MySQL.

## Rationale

- LTS release line.
- Stable target for a new application.
- Longer support horizon than MySQL 8.0.
- Compatible with Laravel 13 and PHP 8.3+.
- Avoids starting on a database release that has reached end of life.
- Keeps the approved MySQL architecture without introducing another database engine.

## Consequences

- New environments must provision MySQL 8.4 LTS.
- CI must use a MySQL 8.4 service.
- Database compatibility checks must run against MySQL 8.4.
- Existing unrelated MySQL installations on developer machines are not modified or migrated.
- SQLite is not a substitute for Foundation persistence tests.

## Compatibility

- Laravel: 13.x.
- PHP: 8.3+.
- PDO: `pdo_mysql`.
- Charset: `utf8mb4`.
- Collation: a current MySQL 8.4 compatible `utf8mb4` collation, selected by the environment/configuration without business-specific data.

## Migration Impact

There is no existing application database to migrate. Checkpoint A created only the standard Laravel Foundation tables in a new `agenda_estetica` database. No business data was created or moved.
