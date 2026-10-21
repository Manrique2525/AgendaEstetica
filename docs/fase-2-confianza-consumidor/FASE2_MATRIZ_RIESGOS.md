# FASE 2 — Matriz de Riesgos de Confianza del Consumidor

## Portada

| Campo | Valor |
|---|---|
| Proyecto | AgendaEstetica — Salón y Barbería Yaris |
| Fase | Fase 2 — Confianza del consumidor |
| Documento padre | [`FASE2_REPORTE_CONFIANZA_CONSUMIDOR.md`](./FASE2_REPORTE_CONFIANZA_CONSUMIDOR.md) |
| Commit inspeccionado | `34c274fdcf294e2386922f9856f42c9044e8176a` |
| Fecha | 2026-10-20 |
| Naturaleza | Auditoría documental. **No implementa cambios.** |

## 1. Metodología de puntuación

### Escala de probabilidad (P)

| P | Criterio |
|---|---|
| 1 | Muy improbable: requiere condiciones excepcionales no observadas |
| 2 | Improbable: posible pero poco frecuente bajo operación normal |
| 3 | Posible: puede ocurrir con configuración/uso habitual |
| 4 | Probable: ocurriría en condiciones comunes si no se mitiga |
| 5 | Muy probable: ocurre de forma habitual sin control |

### Escala de impacto (I)

| I | Criterio para la confianza del consumidor y el negocio |
|---|---|
| 1 | Insignificante: sin efecto perceptible |
| 2 | Menor: molestia o pérdida de confianza leve |
| 3 | Moderado: afecta privacidad/experiencia de forma perceptible |
| 4 | Mayor: exposición de datos o perjuicio reputacional relevante |
| 5 | Severo: daño grave a la privacidad o incumplimiento normativo |

### Cálculo del nivel de riesgo

`Nivel = P × I`. Bandas:

| Rango | Banda |
|---|---|
| 1–4 | Baja |
| 5–9 | Media |
| 10–16 | Alta |
| 17–25 | Crítica |

### Criterio de clasificación

- **Confirmado:** el hecho subyacente está verificado en el código del commit.
- **Potencial:** existe evidencia de que puede ocurrir, pero depende de
  condiciones no verificables desde el repositorio.
- **Pendiente de validar:** requiere comprobación fuera del repositorio
  (producción, hosting, operación).

## 2. Matriz de riesgos

| ID | Activo / proceso | Riesgo y escenario | Causa / debilidad | Evidencia comprobable | P | I | Nivel | Banda | Consecuencia para la confianza | Mitigación propuesta | Prioridad | Estado |
|---|---|---|---|---|---|---|---|---|---|---|---|---|
| R-001 | Datos de clientes (`customers`) | Exposición de PII si se compromete la base | Nombre/teléfono en texto plano, sin cifrado | `database/migrations/2026_09_08_100005_create_customers_table.php:13-15`; `app/Models/Customer.php` | 3 | 4 | 12 | Alta | Pérdida de privacidad y confianza; posible daño reputacional | Evaluar cifrado de campos o minimización (ADR) | Alta | Confirmado |
| R-002 | Configuración de producción | Errores detallados expuestos por `APP_DEBUG=true` | `.env.example` trae `APP_DEBUG=true` (valor de desarrollo) | `.env.example:4` | 2 | 4 | 8 | Media | El usuario ve trazas internas; reduce confianza y seguridad | Forzar `APP_DEBUG=false` en producción y verificar | Alta | Pendiente de validar |
| R-003 | Cookie de sesión admin | Cookie sin flag `Secure` viaja en claro si no hay HTTPS forzado | `SESSION_SECURE_COOKIE=false` en `.env.example`; `config/session.php:172` sin default forzado | `.env.example:32`; `config/session.php:172` | 2 | 4 | 8 | Media | Interceptación de sesión en redes inseguras | Forzar `SESSION_SECURE_COOKIE=true` y HTTPS | Alta | Pendiente de validar |
| R-004 | Contrato de errores de API | Fuga de detalle interno en respuestas 4xx | `bootstrap/app.php:47` devuelve `$exception->getMessage()` para 4xx no excluidos | `bootstrap/app.php:34-49` | 3 | 2 | 6 | Media | Mensajes internos confunden o informan al atacante | Mensajes controlados por tipo de excepción | Media | Confirmado |
| R-005 | Ciclo de vida de datos | Datos retenidos indefinidamente sin política de borrado | Sin columnas de retención/soft-delete ni política documentada | Migraciones `customers`, `appointments`; ausencia en `docs/` | 3 | 3 | 9 | Media | El consumidor no puede ejercer control sobre sus datos | Definir retención, borrado y procedimiento | Media | Confirmado |
| R-006 | Sesión admin | Configuración de sesión no revisada explícitamente en prod. | `lifetime=120`; `same_site=lax`; `http_only=true` por defecto, dependientes de entorno | `config/session.php:35,185,202` | 2 | 2 | 4 | Baja | Sesión más larga/permisiva de lo deseado | Revisar parámetros según operación | Media | Pendiente de validar |
| R-007 | Consentimiento | Consentimiento de Términos no es válido legalmente | Texto provisional; el banner académico usa almacenamiento de cliente | `TermsAndConditionsPage.vue:13,51-53`; `TermsConsentBanner.vue` | 2 | 3 | 6 | Media | Expectativa de protección legal no satisfecha | Definir mecanismo de consentimiento o rotular como académico | Media | Confirmado |
| R-008 | Disponibilidad pública | Enumeración del catálogo/horarios por endpoint público | Endpoint de disponibilidad accesible sin cuenta | `routes/api.php:25-27`; `PublicBookingController` | 2 | 2 | 4 | Baja | Competidores o bots podrían recopilar horarios | Mantener throttling y monitoreo | Baja | Confirmado |
| R-009 | Almacén de sesiones | Registro de `ip_address` y `user_agent` de administradores | Sesión en base de datos persiste metadatos | `config/session.php:21`; migración de `sessions` | 3 | 2 | 6 | Media | Retención de metadatos no gobernada | Incluir en política de retención | Baja | Confirmado |
| R-010 | Correo transaccional | Sin envío real de correo (`MAIL_MAILER=log`) | Configuración por defecto del proyecto | `.env.example:48` | 2 | 2 | 4 | Baja | El consumidor no recibe confirmación por correo | Confirmar expectativa de negocio | Baja | Confirmado |
| R-011 | Autoservicio del cliente | Cliente no puede gestionar su cita ni verificar propiedad del teléfono | Diseño V1 sin cuentas de cliente | `PublicBookingPage.vue`; router sin rutas de cliente autenticado | 3 | 3 | 9 | Media | Experiencia limitada; posible reclamación por terceros | Evaluar cuentas/verificación en roadmap | Media | Confirmado |
| R-012 | Integridad de ficheros | `robots.txt`/crawlers podrían indexar demos | Contenido demostrativo público enlazado | `public/demo/factura-demostracion-sin-validez-fiscal.pdf` | 2 | 2 | 4 | Baja | Confusión si una demo se toma como real | Mantener rotulación de demo | Baja | Confirmado |
| R-013 | Privacidad de la reserva | Nombre/teléfono editables sin verificación; posible suplantación | Sin autenticación de cliente | `PublicBookingPage.vue:120`; `CreatePublicBooking.php` | 3 | 3 | 9 | Media | Reservas a nombre de terceros | Verificación ligera o confirmación | Media | Confirmado |
| R-014 | Transparencia fiscal | Riesgo de que la demo fiscal se confunda con factura real | Artefacto PDF demostrativo | `DemoOrderPage.vue:161-162`; PDF rotulado | 1 | 3 | 3 | Baja | Confusión fiscal del consumidor | Mantener rótulos; revisar legalmente | Baja | Confirmado |
| R-015 | Hosting / transporte | Cabeceras de seguridad o redirección HTTPS ausentes | No verificable desde el repositorio | Fuera de alcance (documentación de despliegue) | 2 | 4 | 8 | Media | Interceptación o clickjacking potencial | Verificar HTTPS/HSTS/cabeceras en el hosting | Media | Pendiente de validar |
| R-016 | Panel admin: profesionales | Creación/edición de profesional con servicios inexistentes | Validación `Rule::exists('services','id')` en Form Request | `AdminProfessionalRequest.php:26` | 2 | 3 | 6 | Media | Profesional ofertando servicios que no existen en catálogo | Validación en servidor ya presente; probar intento de asignación inválida | Media | Confirmado |
| R-017 | Panel admin: profesionales | Eliminación de profesional con citas futuras | Comprobación `appointments()->exists()` devuelve 409 | `AdminProfessionalController.php:99-103` | 2 | 3 | 6 | Media | Pérdida de integridad referencial; citas huérfanas | Regla de negocio en servidor ya implementada | Media | Confirmado |
| R-018 | Panel admin: horarios | Horarios con solapamiento en mismo día | `ProfessionalScheduleValidator` valida no solapamiento | `ProfessionalScheduleValidator.php:49-63` | 2 | 3 | 6 | Media | Disponibilidad inconsistente para reservas | Validación en servidor ya presente | Media | Confirmado |
| R-019 | Panel admin: horarios | Cambio de horarios que invalida reservas existentes | Aviso en UI + `ReplaceProfessionalSchedule` transaccional | `AdminProfessionalSchedulesPage.vue:198`; `ReplaceProfessionalSchedule.php` | 3 | 2 | 6 | Media | Reservas existentes quedan fuera de horario | Aviso visible; no borra citas | Media | Confirmado |
| R-020 | Panel admin: negocio | Zona horaria inválida en perfil | `IanaTimezoneValidator` + validación Laravel `timezone` | `AdminBusinessProfileRequest.php:24`; `IanaTimezoneValidator.php` | 2 | 3 | 6 | Media | Disponibilidad calculada en zona horaria incorrecta | Validación en cliente y servidor | Media | Confirmado |
| R-021 | Seeder demo | Ejecución accidental en producción | Guarda `APP_ENV=production` y `config('app.env')` | `BookingDemoSeeder.php:37-39` | 1 | 4 | 4 | Baja | Datos demo sobrescribiendo reales en prod | Guarda doble en seeder + condicional en DatabaseSeeder | Baja | Confirmado |
| R-022 | Seeder demo | Datos demo presentados como reales | Prefijo `[DEMO]` en nombres; `(DEMO)` en perfil | `BookingDemoSeeder.php:17,80,117,125,131` | 1 | 2 | 2 | Baja | Confusión entre datos demo y reales | Prefijos explícitos; no se usa en producción | Baja | Confirmado |
| R-023 | Dependencias | CVE-2026-108082 en symfony/http-foundation v7.4.18 | Versión afectada (fix en v7.4.19) | `composer show symfony/http-foundation`; `composer audit` | 3 | 3 | 9 | Media | ReDoS vía Cache-Control header | Actualizar a v7.4.19+ (validar compatibilidad Laravel 13) | Alta | Pendiente de validar |

## 3. Lectura de la matriz

- **Riesgos de banda Alta:** R-001 (PII en claro), **R-023 (CVE-2026-108082)**.
- **Riesgos de banda Media:** R-002, R-003, R-004, R-005, R-007, R-009, R-011,
  R-013, R-015, **R-016, R-017, R-018, R-019, R-020, R-023**.
- **Riesgos de banda Baja:** R-006, R-008, R-010, R-012, R-014, R-021, R-022.
- **Ninguno alcanza banda Crítica.**
- Los riesgos **pendientes de validar** (R-002, R-003, R-006, R-015, **R-023**) dependen de
  la configuración real de producción, no comprobable desde el repositorio.

## 4. Relación con los hallazgos del informe

| Riesgo | Hallazgo asociado |
|---|---|
| R-001 | H-07 |
| R-002 | H-08 |
| R-003 | H-09 |
| R-004 | H-10 |
| R-005 | H-11 |
| R-007 | H-12 (transparencia provisional) |
| R-011, R-013 | H-13 |
| R-003, R-006 | H-14 |
| **R-016, R-017** | **Implementación: validación servicios / integridad citas** |
| **R-018, R-019** | **Implementación: validación horarios / aviso impacto** |
| **R-020** | **Implementación: validación zona horaria** |
| **R-021, R-022** | **Implementación: seeder demo protegido / etiquetado** |
| **R-023** | **CVE-2026-108082** |

## 5. Nota de honestidad

Ninguna fila describe explotación comprobada. La puntuación es analítica y se
basa en la evidencia citada. Donde no fue posible verificar fuera del
repositorio, el estado se marca como **Pendiente de validar**.
