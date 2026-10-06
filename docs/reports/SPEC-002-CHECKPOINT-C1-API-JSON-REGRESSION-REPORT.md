# SPEC-002 Checkpoint C.1 API JSON Regression Report

## 1. Repository state

- Branch: `feat/spec-002-ux-design-system`.
- Base before C.1: `2ae3144`.
- SPEC-001: `CLOSED`.
- SPEC-002: `APPROVED FOR DEVELOPMENT / IN PROGRESS`.
- Checkpoint C: `COMPLETED`.
- Checkpoint C.1: authorized.
- Checkpoint D: not authorized.
- Working tree before C.1: clean.

## 2. Documentation/code reviewed

Reviewed:

- `AGENTS.md`.
- `docs/MASTER_TECHNICAL_SPEC.md`.
- `docs/architecture/ARCHITECTURE.md`.
- `docs/specs/SPEC-001-project-foundation.md`.
- `docs/specs/SPEC-002-ux-design-system.md`.
- `docs/reports/SPEC-001-CHECKPOINT-C-REPORT.md`.
- `docs/reports/SPEC-001-IMPLEMENTATION-REPORT.md`.
- `docs/reports/SPEC-002-CHECKPOINT-C-REPORT.md`.
- `bootstrap/app.php`.
- `routes/api.php`.
- `routes/web.php`.
- `tests/Feature/Api/FoundationTest.php`.
- `tests/Feature/Api/AdminAuthenticationTest.php`.

## 3. Original defect reproduction

Before the fix:

```text
GET /api/v1/admin/auth/me
without Accept header
```

reproduced the inherited guest redirect failure.

## 4. Original response without Accept

```text
HTTP 500 Internal Server Error
Content-Type: application/json
Body: {"message":"Server error."}
```

The application log showed `Route [login] not defined` during guest redirect resolution.

## 5. Root cause

The existing `shouldRenderJsonWhen()` rule handled exception rendering, but the `auth:sanctum` middleware attempted to resolve the default guest redirect before that rendering path. With no `Accept` header, Laravel tried `route('login')`; the SPA’s real route is `/admin/login` and no backend `/login` route exists.

## 6. Exception-rendering strategy

The existing API exception strategy was preserved. `shouldRenderJsonWhen()` continues to classify `api/*` requests as JSON while retaining the safe JSON error renderer and non-API behavior.

## 7. `shouldRenderJsonWhen` configuration

Existing configuration remains:

```php
$exceptions->shouldRenderJsonWhen(
    fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
);
```

No duplicate exception strategy was introduced.

## 8. Guest redirect configuration

Added the Laravel 13 middleware configuration:

```php
$middleware->redirectGuestsTo(
    fn (Request $request): ?string => $request->is('api/*') ? null : '/admin/login',
);
```

API guest requests now throw/render JSON authentication failures without a redirect. Non-API guest requests retain the SPA login target.

## 9. Fake login-route audit

No `/login` route was created. `php artisan route:list --except-vendor` contains no fake backend login route.

## 10. `/me` without Accept result

```text
GET /api/v1/admin/auth/me
→ 401
→ application/json
→ {"message":"Unauthenticated."}
```

## 11. `/me` with Accept result

```text
GET /api/v1/admin/auth/me
Accept: application/json
→ 401
→ application/json
```

## 12. Logout without Accept result

```text
POST /api/v1/admin/auth/logout
without session or Accept header
→ 401 JSON
```

## 13. Unknown API without Accept result

```text
GET /api/v1/non-existent
→ 404 JSON
```

## 14. Unknown API with Accept result

```text
GET /api/v1/non-existent
Accept: application/json
→ 404 JSON
```

## 15. SPA route regression

```text
GET /: 200 SPA shell
GET /admin/login: 200 SPA shell
```

No SPA fallback behavior changed.

## 16. API fallback regression

`/api/v1/non-existent` remains a JSON 404 and never falls through to the SPA shell.

## 17. Route audit

Route list remains limited to the existing technical routes:

- `/` SPA shell.
- `api/v1/admin/auth/login`.
- `api/v1/admin/auth/logout`.
- `api/v1/admin/auth/me`.
- `api/v1/health`.
- SPA fallback path.

No `/login` route was added.

## 18. Tests created/modified

Modified:

- `tests/Feature/Api/FoundationTest.php`.
- `tests/Feature/Api/AdminAuthenticationTest.php`.

Added coverage for `/me` without/with `Accept`, logout without `Accept`, and unknown API routes without/with `Accept`.

## 19. Pest result

```text
php artisan test: PASS
12 tests passed, 58 assertions
```

## 20. Larastan result

```text
vendor/bin/phpstan analyse: PASS
```

## 21. Pint result

```text
vendor/bin/pint --test: PASS
```

## 22. Frontend test result

```text
npm run test: PASS
10 files, 24 tests passed
```

## 23. ESLint result

```text
npm run lint: PASS
```

## 24. Typecheck result

```text
npm run typecheck: PASS
```

## 25. Build result

```text
npm run build: PASS
```

## 26. Security audit

- API unauthenticated requests do not redirect.
- No fake backend login route was added.
- No auth logic, Sanctum, CSRF, session or rate limiting behavior was weakened.
- No stack traces, filesystem paths or secrets are exposed.
- No frontend auth storage or remote dependency changes were made.

## 27. Scope audit

```text
API JSON exception rule:       YES
Guest redirect correction:     YES
Regression tests:              YES
Documentation/report:          YES
Visual changes:                NO
Design token changes:          NO
Component changes:             NO
Router changes:                NO
Auth business logic changes:   NO
New dependencies:              NO
Business functionality:        NO
Checkpoint D work:             NO
```

## 28. Files modified

- `bootstrap/app.php`.
- `tests/Feature/Api/FoundationTest.php`.
- `tests/Feature/Api/AdminAuthenticationTest.php`.
- `docs/specs/SPEC-002-ux-design-system.md`.
- `docs/roadmap/ROADMAP.md`.

## 29. Files created

- `docs/reports/SPEC-002-CHECKPOINT-C1-API-JSON-REGRESSION-REPORT.md`.

## 30. Files removed

None.

## 31. Checkpoint C.1 status

```text
CHECKPOINT C.1: COMPLETED
```

The defect was inherited from Foundation, discovered during C regression validation, and was not introduced by C visual integration.

## 32. Commits created

Fix commit:

```text
fix: enforce JSON responses for API authentication
```

Documentation/report commit:

```text
docs: report SPEC-002 checkpoint C.1 API JSON regression
```

## 33. Commit hashes

```text
Fix: `87cdd3f`
Documentation/report: recorded in final Git verification
```

## 34. Push result

Both commits are to be published normally to `origin/feat/spec-002-ux-design-system`; no force push or main push is performed.

## 35. Working tree

Final verification must show a clean tree synchronized with the remote branch.

## 36. SPEC-002 status

```text
APPROVED FOR DEVELOPMENT / IN PROGRESS
```

Checkpoint C and C.1 are complete. Checkpoint D remains unauthorized.

## 37. Remaining blockers

- Entire-SPEC blockers: none.
- Checkpoint D requires explicit authorization.

## 38. Recommended next action

Review the API JSON regression correction, then decide whether Checkpoint D may be authorized. No further work is started automatically.
