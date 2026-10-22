# SPEC-009 HUMAN BROWSER QA CHECKLIST

## Instructions

Run the application in a real browser. Record the browser name/version,
application URL, date, result and notes. Do not mark this checklist complete
from unit tests, source inspection or a production build alone.

## Landing Viewports

| Viewport | Header | Menu | Hero/content | CTAs | Tienda | Servicios | Contacto | Footer | Overflow | Console/errors | Result | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 375px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 390px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 768px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 1024px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 1440px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 1920px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |

Verify no clipped text, excessive stretching, unreadable type, CTA collision,
or horizontal overflow. At 1920px confirm reasonable line lengths and content
max-width. At 375px confirm the identity and menu do not collide.

## `/reservar` Viewports

| Viewport | Header | Menu | Booking content | Controls | Footer | Overflow | Console/errors | Result | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- | --- | --- |
| 390px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 768px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |
| 1440px | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | [ ] | |

Confirm the existing booking flow remains readable and its behavior is
unchanged. Do not alter booking data or submit a real appointment as part of
this shell review.

## Mobile Menu

- [ ] Menu button is visible at mobile width.
- [ ] Menu opens and closes.
- [ ] `aria-expanded` changes correctly.
- [ ] `aria-controls` points to the rendered navigation.
- [ ] Escape closes the menu.
- [ ] Selecting Tienda, Servicios or Contacto closes the menu.
- [ ] Selecting Solicitar cita closes the menu.
- [ ] Focus remains understandable and no overlay causes overflow.
- [ ] Desktop navigation appears without a duplicate visible menu.

Repeat from `/` and `/reservar`.

## Keyboard and Skip Link

- [ ] First Tab exposes the skip link.
- [ ] Activating it moves focus to `#main-content`.
- [ ] Header identity link is keyboard reachable.
- [ ] Navigation links are keyboard reachable.
- [ ] CTAs are keyboard reachable and have link semantics.
- [ ] WhatsApp link is keyboard reachable and clearly external.
- [ ] Focus indicators are visible against the approved dark surface.
- [ ] Heading hierarchy is understandable and there is one H1.

## Hash Navigation

From `/`:

- [ ] Tienda reaches `#tienda`.
- [ ] Servicios reaches `#servicios`.
- [ ] Contacto reaches `#contacto`.

From `/reservar`:

- [ ] Tienda reaches `/#tienda`, not `/reservar#tienda`.
- [ ] Servicios reaches `/#servicios`, not `/reservar#servicios`.
- [ ] Contacto reaches `/#contacto`, not `/reservar#contacto`.

Direct URLs:

- [ ] `/#tienda` reaches a visible meaningful section.
- [ ] `/#servicios` reaches a visible meaningful section.
- [ ] `/#contacto` reaches a visible meaningful section.

## Content and External Contact

- [ ] Technical Foundation copy is absent from the user-facing homepage.
- [ ] Approved name, tagline, hours, phone and location are exact.
- [ ] Mary Kay and Cuidado capilar are preparation-only categories.
- [ ] No product, price, inventory, purchase or payment language appears.
- [ ] WhatsApp href is `https://wa.me/529932294158`.
- [ ] WhatsApp is not contacted on page load.
- [ ] No map, email, social links or unapproved business data appears.

## Console and Network

- [ ] No uncaught browser errors.
- [ ] No unexpected Vue warnings.
- [ ] No failed landing assets or fonts.
- [ ] Landing makes no booking API request on page load.
- [ ] Landing makes no CMS, analytics or tracking request.
- [ ] WhatsApp is contacted only after link activation.

## Known/Future Booking Review

If “No hay servicios disponibles” is confused with “No se pudieron cargar las
opciones”, record it as **outside SPEC-009**, future appointment/booking work.
Do not fix it in Checkpoint C.

## Signoff

- Browser/version: ______________________________
- Application URL: ______________________________
- Tester/date: __________________________________
- Overall result: _______________________________
- Evidence links/notes: _________________________

## Recorded Playwright Evidence

- Browser: Playwright Chromium `153.0.8010.12`.
- Playwright: `1.63.0`.
- Mode: headless.
- Base URL: `http://127.0.0.1:8000`.
- Machine-readable results: `/tmp/spec009-browser-qa/results.json`.
- Human-readable results: `/tmp/spec009-browser-qa/report.txt`.
- Screenshots: `/tmp/spec009-browser-qa/evidence/`.
- Total checks: `45 PASS / 0 FAIL`.
- Screenshots: 10 PNG files covering all required landing and `/reservar` viewports.

The automated real-browser run verified viewport width, rendered landmarks,
approved content, one H1, menu state/Escape/link close, root-safe hash
navigation, direct hashes, skip link, keyboard focusability, WhatsApp
semantics, titles, metadata, console/page errors, network behavior and
booking shell requests. No application or repository dependency changes were
made during QA.
