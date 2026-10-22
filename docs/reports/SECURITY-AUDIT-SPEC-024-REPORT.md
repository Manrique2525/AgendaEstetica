# Security Audit Report — Dependency Advisories

- **Rama:** `chore/security-audit-spec-024`
- **Base:** `origin/main` = `8f7be61ca390b6fe9edc93c4281b07019758f430` (HEAD verificado, árbol limpio antes de modificar)
- **Fecha:** 2026-10-20
- **Alcance autorizado:** investigar, corregir y verificar advisories de dependencias en una rama de seguridad independiente. Sin merge, sin despliegue, sin avance de checkpoint, sin reescritura de historial.
- **Contexto:** los mismos advisories hacían fallar Remote Quality en la rama D (run `39524360317`, fallo únicamente en los pasos "Audit Composer dependencies" y "Audit npm dependencies"); los lockfiles de D y de `origin/main` eran idénticos (0 líneas de diff), por lo que la falla es preexistente y no causada por ningún cambio de código.

## 1. Advisories verificados (estado inicial)

### Composer (`composer audit` exit=1 → 1 advisory)

| Campo | Valor |
|---|---|
| Paquete instalado | `symfony/http-foundation` **v7.4.18** |
| Advisory | `PKSA-xmwy-rmx5-m7h4` (FriendsOfPHP/security-advisories) |
| CVE | `CVE-2026-108082` |
| Título | ReDoS in `HeaderUtils::split()` reachable through the Cache-Control request header |
| Rango afectado (nuestra línea) | `>=7.4.0,<7.4.19` |
| Versión corregida | **7.4.19** (última 7.4.x disponible: 7.4.21) |
| Reportado | 2026-09-15 |
| Dependientes | `laravel/framework` `^7.4.0 \|\| ^8.0.0`, `symfony/http-kernel` `^7.4\|^8.0`, `fruitcake/php-cors` `^5.4\|^6.4\|^7.3\|^8` |

Verificación de restricción: `composer why-not symfony/http-foundation 7.4.21` confirma que **ningún** paquete instalado impide 7.4.21 → actualización dentro de la línea 7.4 ya en uso, sin tocar otros paquetes symfony.

### npm (`npm audit` exit=1 → 3 vulnerabilities: 2 critical, 1 high)

| # | Paquete | Instalado | Advisory(s) | Severidad | Rango afectado | Corregido en |
|---|---|---|---|---|---|---|
| 1 | `shell-quote` | 1.9.0 (fijado **exacto** por `concurrently@9.2.4`) | `GHSA-pqg4-j6r4-53mv` — `quote()` command injection via line terminator after a `{ comment }` token | critical | `>=1.8.4 <1.11.0` | **1.11.0** (aplicado: 1.12.0) |
| 2 | `concurrently` | 9.2.4 | derivado vía `shell-quote` | critical | `9.2.3 - 9.2.4 \|\| 10.0.0 - 10.0.5` | **9.2.5** (depende de `shell-quote@1.12.0`, verificado con `npm view`) |
| 3 | `vite` | 8.2.2 | `GHSA-698v-4r3x-3vcv` (escapeCmdArgs omits `%` escaping, Windows), `GHSA-r825-w8mm-5pxw` (editor CLI flag misinterpretation), `GHSA-587h-7wmj-h5pm` (`fs.existsSync()` bypass) — todas vía `launch-editor` | high | `>=8.2.0 <=8.2.3` (no existe `8.2.3` publicado) | **8.2.4** (primera versión parche) |

Nota: `concurrently` y `vite` están en `devDependencies`; las advisories de `launch-editor` afectan a herramientas de desarrollo y su componente explícito es Windows-specific, pero el gate `npm audit` falla en cualquier plataforma.

## 2. Cambios mínimos aplicados

| Archivo | Cambio |
|---|---|
| `composer.json` | sin cambios |
| `composer.lock` | `symfony/http-foundation` v7.4.18 → **v7.4.21** (único paquete; `composer update "symfony/http-foundation:7.4.21" --dry-run` previo confirmó un solo upgrade) |
| `package.json` | `concurrently` `^9.2.4` → `^9.2.5`; `vite` `^8.0.0` → `^8.2.4` (documenta la mínima versión segura) |
| `package-lock.json` | exactamente 3 cambios de versión: `concurrently` 9.2.4→9.2.5, `shell-quote` 1.9.0→1.12.0, `vite` 8.2.2→8.2.4; +6 entradas opcionales de `@tailwindcss/oxide-wasm32-wasi` (materialización de dependencias opcionales por la resolución de npm); `lockfileVersion` 3 sin cambio; y eliminación de la línea `"name": "agenda_yaris"` del entry raíz del lock (dato obsoleto: `package.json` no declara `name`; sin impacto en la instalación) |

Verificación de alcance: comparación programada de `package-lock.json` antes/después (adds/removes/version-changes) — 0 paquetes eliminados, 3 versiones cambiadas, 6 entradas opcionales añadidas, y 2 cambios más en la raíz del lock: los rangos de `package.json` reflejados y la eliminación del campo `"name": "agenda_yaris"` (obsoleto, ya que `package.json` no declara `name`; lo limpió npm 11; sin impacto en `npm ci`, validado con npm 10.8.2).

**Herramienta:** npm local 10.8.2 (node 20.20.2) y 10.9.9 **crashean** con bug de arborist (`Cannot read properties of null (reading 'edgesOut')` en `#loadPeerSet`, también en `npm audit fix`) al construir el árbol ideal con este repo. La actualización se aplicó con `npx npm@11.21.1 --package-lock-only` (sin instalar globalmente, sin tocar el entorno) y el lockfile resultante se validó con `npm ci` usando el npm **10.8.2** de la CI (exit 0), confirmando compatibilidad con el `node-version: 20.20.2` de `quality.yml`.

## 3. Auditorías post-corrección

```text
composer audit → exit 0 · "No security vulnerability advisories found"
npm audit      → exit 0 · "found 0 vulnerabilities"
```

## 4. Quality gates ejecutados (AGENTS.md)

| Gate | Resultado |
|---|---|
| `composer validate --strict` | PASS |
| `composer audit` | PASS (0) |
| `vendor/bin/pint --test` | PASS |
| `vendor/bin/phpstan analyse` | PASS (0 errores) |
| `php artisan test` | PASS — 208 tests / 1202 assertions |
| `npm run typecheck` | PASS (exit 0) |
| `npm run lint` | PASS (exit 0) |
| `npm run test` | PASS — 26 files / 104 tests (baseline de `main`, sin las pruebas de D) |
| `npm run build` | PASS (exit 0) |
| `npm audit` | PASS (0) |
| `git diff --check` | limpio |

Remote Quality en el SHA publicado: verificado en la entrega final de esta auditoría (el workflow no cubre `chore/**` en `push`; se dispara mediante PR abierto en modo draft **sin fusionar**, trigger `pull_request:` de `quality.yml`).

## 5. Riesgos residuales

- Parches semver-compatibles dentro de las líneas ya declaradas (7.4.x / 9.2.x / 8.2.x); ninguna funcionalidad de negocio tocada. Cobertura de regresión: 208 tests backend + 104 frontend PASS.
- Bug de arborist (npm ≤10.9.9): cualquier `npm install`/`npm audit fix` en este repo crashea; workaround documentado (`npx npm@11`). `npm ci` (lo que usa la CI) funciona. No resuelto aquí: es un defecto del tooling, no del proyecto.
- La rama D conserva lockfiles sin parchear: Remote Quality de D seguirá fallando en los steps de auditoría hasta que esta rama de seguridad se integre (merge a `main` o a D) — decisión humana.
- Las advisories de `launch-editor` eran Windows-specific y de dev tooling; se parchearon igualmente porque el gate es severidad-cero.

## 6. Estado y límites respetados

- Sin merge a `main`, sin despliegue, sin force-push ni reescritura de historial.
- `main` intacto en `8f7be61`; rama D intacta en `d48a564bf0a83a8369357f89fe95b8993b6247f6` (corrección de honestidad XSD sin revertir).
- D **no** declarada aprobada: su Remote Quality permanece fallando en auditorías.
- Checkpoint E no avanzado. Este reporte queda sujeto a aprobación humana para integrar.
