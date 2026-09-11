# Plan de Implementación — Aleph Platform (backend + admin)

> **Decisiones locked:**
> 1. Proyecto **nuevo repositorio** (`aleph-platform`), `fotoaleph` se archiva como legacy (tag `legacy-eol`, rama `main` congelada).
> 2. **Hosting compartido** cPanel (panel web, SIN SSH). Desarrollo 100% local; deploy manual al final (Fase 7). En local: `CACHE=file`, `QUEUE=database`/`sync`.
> 3. Alcance: **solo backend API + dashboard admin**. Fronts existentes fuera de este repo; **puedo romper contrato** → API versionada `/api/v1`.
> 4. Admin con **Filament v4**, API con **Sanctum + spatie/laravel-permission (teams)**.
> 5. Media **100% pública en cada front**; backend guarda solo metadatos (`url` absoluta). Sin firma.
> 6. Ritmo: **1 dev, sin prisa** (~10 semanas). MySQL. Superadmin global + coordinador por tenant.

---

## 0. Estrategia Git y entornos

- **Repos:** `FotoAleph/aleph-platform` (nuevo, público/privado a tu criterio) + `fotoaleph` archivado (Settings → Archive, README con aviso).
- **Ramas:** `main` (prod, protegida, solo PR), `develop` (integración), `feature/*` por fase. Tags `v1.0.0` por corte. Los `.md` de auditoría/planes se mueven al nuevo repo en `docs/` y aquí quedan como referencia histórica.
- **Entornos:** local (PHP 8.5 + MySQL 8) → `prod` directo (cPanel, Fase 7, sin staging salvo subdominio manual). Deploy **manual**: zip del proyecto (sin `vendor`/`node_modules`/`.env`) + `composer install --no-dev` si el panel lo permite, o subida completa; `phpMyAdmin` para importar SQL; cron por interfaz cPanel.
- **CI mínima (GitHub Actions, gratis):** `pint --test` + `pest` con sqlite `:memory:` + `phpstan` nivel 6. Sin CI no hay merge a `main`.

## 1. Fase 0 — Verificación local (Semana 1, 3-5 h)

Objetivo: confirmar que la máquina local replica lo que el cPanel exige, sin SSH.

- [ ] **T0.1** Verificación local (correr en tu máquina, pegar salida en un issue):
  ```bash
  php -v && php -m | grep -Ei 'bcmath|ctype|fileinfo|json|mbstring|openssl|pdo_mysql|tokenizer|xml|curl|gd|intl|zip|exif'
  mysql --version
  ```
  Criterio: PHP 8.2+ (tienes 8.5 ✔), `pdo_mysql` presente.
- [ ] **T0.2** Verificación visual en cPanel (panel web, sin terminal): versión PHP del selector, versión MySQL/phpMyAdmin, cuota de disco, que el panel permite cron y certificados SSL. Anotar en el issue: PHP __, MySQL __, cron sí/no.
- [ ] **T0.3** Crear repo `aleph-platform`, `composer create-project laravel/laravel:^12`, `.env` solo local (no en git), gitleaks en CI.
- [ ] **T0.4** Congelar `fotoaleph`: mergear rama activa o descartarla, tag `legacy-eol`, archivar.

## 2. Fase 1 — Núcleo multi-tenant + auth (Semanas 2-3)

- [ ] **T1.1** Instalar: `laravel/sanctum`, `spatie/laravel-permission` (`teams => true`), `filament/filament`, `dedoc/scramble`, `pestphp/pest`, `larastan/larastan`.
- [ ] **T1.2** Migraciones `tenants` (`slug/domain/status/settings JSON`) + `sites` (`tenant_id` FK, `slug` único por tenant) + seed de los 5 tenants y dominios reales.
- [ ] **T1.3** `TenantContext` (resuelve por `X-Tenant` header o `{tenant:slug}` o dominio) + `ResolveTenant` middleware + `BelongsToTenant` trait + `TenantScope` global. **Toda tabla de negocio con `tenant_id`.**
- [ ] **T1.4** Spatie teams: roles `superadmin` (global), `coordinador|cliente` (por team); `Gate::before` superadmin; `RoleSeeder` + `PermissionSeeder` (`catalog.*`, `people.*`, `crm.*`, `tenants.manage`).
- [ ] **T1.5** Auth API: `POST /api/v1/auth/{register,login,logout}` — register crea `cliente` del tenant resuelto (jamás superadmin); login emite token con abilities `tenant:{id}:*`.
- [ ] **T1.6** `TenantIsolationTest` (Pest): coordinador A no ve ni escribe en tenant B; cliente no crea; superadmin sí. **Rojo→verde obligatorio antes de Fase 2.**

## 3. Fase 2 — Catálogo + media pública (Semanas 4-5)

Unifica JyM (`proyectos/categorías/grupos`) y Casa Ángel (`eventos/muestrarios/ocasiones/temáticas/colores`).

- [ ] **T2.1** Tablas `taxonomies(kind=category|group|occasion|theme|color|tag)` + `catalog_items(kind=project|event|sample)` + pivot `catalog_item_taxonomy` + `media(url absoluta del front, preview_url, type, alt, level, meta JSON)` + `mediables`.
- [ ] **T2.2** API pública (con `throttle:api` + `cursorPaginate`): `GET /api/v1/t/{slug}/catalog`, `GET .../catalog/{item:slug}`, filtros `?kind=&taxonomy=&q=`; Scribe/OpenAPI publicado.
- [ ] **T2.3** API privada (`auth:sanctum + can`): CRUD catálogo/taxonomías/media-metadata. **Media v1 = solo URLs** (Filament `TextInput::url`, validación `active_url`, nombres UUID generados en el front). Sin subida de binarios al backend (fase posterior si hace falta).
- [ ] **T2.4** Seeders demo por tenant (3 proyectos JyM, 3 eventos + 3 muestrarios Casa Ángel) para que los fronts adapten sus `fetch` sin esperarte.

## 4. Fase 3 — Formación/registro Biotek + Sport (Semana 6)

- [ ] **T3.1** Tablas `people` (absorbe ambos `estudiantes`), `courses` (`talleres`), `enrollments` (pivot + carnet), `questions/options/attempts/answers` (solo Biotek; Sport no las usa).
- [ ] **T3.2** API privada por tenant + exportación CSV (`GET .../people/export`, streaming, sin cargar todo en RAM — límite del compartido).
- [ ] **T3.3** Policies: coordinador gestiona su tenant; cliente solo lectura de lo propio.

## 5. Fase 4 — CRM transversal (Semana 7)

- [ ] **T4.1** `pqrs/quotes/messages` con `tenant_id` + endpoints públicos `POST /api/v1/t/{slug}/{pqrs|messages}` (throttle 10/min + honeypot + validación estricta) y gestión privada.
- [ ] **T4.2** `social_links + addresses + phones` (polimórficas, `tenant_id` nullable para globales).
- [ ] **T4.3** Notificaciones por **mail** (cPanel SMTP) en queue `database` + `schedule:run` cada minuto; reintentos 3, `failed_jobs` vigilado desde Filament.

## 6. Fase 5 — Admin Filament (Semanas 8-9)

- [ ] **T5.1** Panel `admin.*`: login sesión (separado de tokens API), Resources `Tenant, Site, CatalogItem, Taxonomy, Media, Person, Course, Pqr, Quote, Message, User`.
- [ ] **T5.2** Scoping por team: `getEloquentQuery()` filtra por tenant del coordinador; `Tenant` resource solo superadmin; `User` invite crea dentro del team (nunca superadmin desde UI coordinador).
- [ ] **T5.3** Widgets: conteos por tenant, últimos PQR/mensajes, `failed_jobs`. Traducción ES donde sea visible al cliente.
- [ ] **T5.4** UAT con 1 coordinador real por tenant (JyM y Casa Ángel primero).

## 7. Fase 6 — Endurecer + go-live local (Semana 10)

- [ ] **T6.1** CORS allowlist exacta de los 5 dominios + `admin.*` (sin `*`), `throttle` global, `APP_DEBUG=false`.
- [ ] **T6.2** Corte por tenant en local (orden libre, re-publicación): seed prod mínimo, checklist humo (login, CRUD, formulario público, 404/403), tag `v1.x`.
- [ ] **T6.3** Cierre código: `fotoaleph` archivado, doc `docs/CONTRATO_API.md` para los fronts (base URL, auth, paginación, errores `{message,errors}`).

## 8. Fase 7 — Deploy manual a cPanel (Semana 11, solo panel web)

- [ ] **T7.1** En cPanel: crear DB + usuario, importar `database/schema-prod.sql` (exportado en local via `mysqldump`/phpMyAdmin), subdominios `api.*` y `admin.*` → `public/`, SSL, variables `.env` en el panel.
- [ ] **T7.2** Subir proyecto (ZIP sin `vendor`/`node_modules`/`.env` + `composer install` si el panel lo permite; si no, ZIP completo), crear `storage` link según docs del proveedor, permisos `storage/` y `bootstrap/cache/` escribibles.
- [ ] **T7.3** Cron por interfaz cPanel → `php /home/USER/public_html/artisan schedule:run` cada minuto (queue `database`, mails, `media:check`); verificar `failed_jobs` en Filament.
- [ ] **T7.4** Backups: descarga semanal de DB (phpMyAdmin) + ZIP de `storage/`; retención local 4 copias + prueba de restore documentada (1 página).

## 8. Riesgos (compartido + 100% público)

1. **Vecino ruidoso / límites cPanel** (RAM, `max_execution_time`): paginación cursor + exports streaming + queue `database` con `--sleep=3 --tries=3`; nada de jobs pesados ni `::get()` sin límite (regla phpstan).
2. **Media huérfana** (front borra archivo, backend guarda URL): validación `active_url` al guardar + comando `media:check --tenant=` que reporta 404s semanalmente.
3. **Scope olvidado**: CI bloquea merge si `TenantIsolationTest` falla; ningún modelo de negocio sin `BelongsToTenant`.
4. **Credenciales en git**: gitleaks en CI + `.env` solo en cPanel + `config:cache` tras cada deploy.
5. **Un solo dev**: fases sin dependencias cruzadas; si algo se atasca (ej. T3 quizzes), se recorta a `people/courses` y se publica igual — el plan lo permite.

## 9. Cronograma (1 dev, ~6 h/sem)

| Sem | Fase | Hito |
|---|---|---|
| 1 | 0 | Hosting verificado + repo creado + fotoaleph congelado |
| 2-3 | 1 | Auth + tenancy + isolation test verde |
| 4-5 | 2 | Catálogo + media-metadata + OpenAPI |
| 6 | 3 | People/courses (+quizzes si da tiempo) |
| 7 | 4 | CRM + mails en queue |
| 8-9 | 5 | Filament + UAT coordinadores |
| 10 | 6 | Hardening + go-live local + tag v1 |
| 11 | 7 | Deploy manual cPanel + backups + cron por panel |

Siguiente acción: ejecuta **T0.1** en tu máquina y anota **T0.2** desde el panel; con eso genero el scaffold (composer + migraciones 001 + `TenantContext`) en `develop`.
