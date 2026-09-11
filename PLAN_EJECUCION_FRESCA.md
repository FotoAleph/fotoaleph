# Plan de Ejecución Fresca — Aleph Platform (sin migración de datos)

> Decisiones locked 2026-09-11: **MySQL · superadmin + coordinador/tenant · re-publicación total · 4 dominios + admin · cero datos legacy.**

## 1. Qué cambia vs el plan original
- **Fuera:** ETL, `legacy_map`, dual-run, tabla `redirects`, rollback por DNS, fase Biotek-histórica.
- **Dentro:** build directo en repo nuevo, seeders demo por tenant, carga inicial manual, deploy por sitio cuando esté listo (orden libre).

## 2. Stack final
- `aleph-platform/backend`: Laravel 12, PHP 8.4, MySQL 8, Redis (queue/cache). **Sin S3/MinIO ni VPS de media (decisión costos 2026-09-11).**
- Media **descentralizada: cada front guarda sus archivos en su propio hosting** (`front-jym/public/media`, etc.). El backend guarda solo metadatos (path relativo, alt, level, meta), no binarios.
- Solo excepción (opcional, costo cero): originales privados de eventos Casa Ángel en `storage/app/private` del backend, servidos por ruta firmada Laravel (ver §3.1). Resto 100% público por front.

## 3. Esquema MySQL v1 (orden de migración)
```
001 tenants(id, slug UNIQUE, name, domain UNIQUE, status ENUM draft|active|suspended, settings JSON NULL)
002 sites(id, tenant_id FK CASCADE, name, slug, description NULL, url NULL, starts_on NULL, ends_on NULL, status ENUM, UNIQUE tenant_id+slug)
003 permission teams (spatie + teams.php 'teams' => true, team_foreign_key 'team_id')
004 media(id, tenant_id FK CASCADE, uuid CHAR36 UNIQUE, disk ENUM('front','backend-private'), path VARCHAR, preview_path NULL, type ENUM image|video|doc, mime NULL, width NULL, height NULL, alt NULL, level INT DEFAULT 0, meta JSON NULL, INDEX tenant+type)
  -- 'front' = físico en el hosting de ese site (público, sin firma). 'backend-private' = solo originales Casa Ángel (firmados, §3.1).
005 mediables(id, media_id FK CASCADE, mediable_type, mediable_id UNSIGNED, tenant_id FK, sort INT DEFAULT 0, meta JSON NULL, INDEX tenant+mediable)

### 3.1 Política de firma (decisión costos 2026-09-11)
- **Regla general: SIN firma.** Catálogos, muestrarios, Biotek/Sport y previews: archivos públicos en cada front (`https://vidrios.../media/uuid.jpg`). Mitigación barata: nombres UUID no predecibles + `noindex` en galerías privadas + `robots.txt`. Asumir: link compartido = acceso permanente.
- **Excepción opcional (recomendada, costo cero): originales de eventos Casa Ángel.** Se guardan en `backend-private` y se sirven por `GET /m/{uuid} → auth:sanctum + signed + can:view` con TTL 10 min para `cliente`. Volumen bajo (pocas fotos por evento), no requiere otro server.
- Si ni eso se quiere pagar (espacio/ancho de banda del backend), desistir 100%: todo público con UUID. Veredicto abajo.
006 taxonomies(id, tenant_id FK CASCADE, kind ENUM category|group|occasion|theme|color|tag, name, slug, description NULL, color_hex NULL, level INT DEFAULT 0, UNIQUE tenant+kind+slug)
007 catalog_items(id, tenant_id FK CASCADE, kind ENUM project|event|sample, slug, title, excerpt NULL, body NULL, code NULL, location NULL, happened_on NULL, delivered_on NULL, status ENUM draft|published|archived DEFAULT draft, featured BOOL DEFAULT 0, level INT DEFAULT 0, meta JSON NULL, UNIQUE tenant+slug, INDEX tenant+kind+status)
008 catalog_item_taxonomy(item_id FK CASCADE, taxonomy_id FK CASCADE, PRIMARY(item+taxonomy))
009 people(id, tenant_id FK CASCADE, kind ENUM student|lead|contact DEFAULT student, first_names, last_names, doc_id NULL, category NULL, meta JSON NULL, UNIQUE tenant+doc_id)
010 courses(id, tenant_id FK CASCADE, code, title, held_on NULL, duration_s NULL, UNIQUE tenant+code)
011 enrollments(id, person_id FK CASCADE, course_id FK CASCADE, status ENUM active|done|expired DEFAULT active, credential_no NULL, issued_on NULL, expires_on NULL, UNIQUE person+course)
012 questions(id, tenant_id FK CASCADE, course_id FK NULL, body TEXT, kind VARCHAR DEFAULT seleccion_unica, level INT DEFAULT 1)
013 options(id, question_id FK CASCADE, body TEXT, is_correct BOOL DEFAULT 0)
014 attempts(id, course_id FK CASCADE, person_id FK CASCADE, nro INT DEFAULT 1, score INT NULL, finished_at NULL)
015 answers(id, attempt_id FK CASCADE, question_id FK, option_id FK)
016 pqrs(id, tenant_id FK CASCADE, user_id FK NULL, subject, body TEXT, status ENUM open|process|closed DEFAULT open)
017 quotes/cotizaciones(id, tenant_id FK CASCADE, user_id FK NULL, title, body TEXT NULL, status ENUM draft|sent|approved|rejected DEFAULT draft, total DECIMAL 12,2 NULL)
018 messages(id, tenant_id FK CASCADE, name, email, phone NULL, body TEXT, read_at NULL)
019 social_network_types(id, name UNIQUE, icon NULL) + social_links(id, tenant_id FK NULL, linkable_type, linkable_id, type_id FK, url)
020 addresses/phones polimórficas con tenant_id NULL-able
```

## 4. AuthZ (superadmin + coordinador/tenant)
- Roles: `superadmin` (global, sin team), `coordinador` (team=tenant), `cliente` (team=tenant).
- `Gate::before(fn($u) => $u->hasRole('superadmin') ? true : null)`.
- Permisos granulares: `catalog.view|create|update|delete`, `people.*`, `crm.*`, `tenants.manage` (solo superadmin).
- API: Sanctum `createToken($tenant->slug, ["tenant:{$id}:catalog:write"])`. Middleware `auth:sanctum` + `ResolveTenant` + `can:…`.
- Invitaciones: el coordinador solo invita dentro de su team (validado por policy, no por front).

## 5. Rutas v1
```
POST /api/v1/auth/{login,register,logout}      → register crea cliente del tenant del dominio, jamás superadmin
GET  /api/v1/t/{tenant:slug}/catalog[/{slug}]  → público si item published, throttle:api
POST /api/v1/t/{tenant:slug}/pqrs|messages     → público con throttle+ honeypot
auth:sanctum → POST/PATCH/DELETE catalog, CRUD people/courses/attempts, admin pqrs/quotes
GET  /api/v1/admin/tenants[/{slug}]            → superadmin
```

## 6. Cronograma 4 semanas (1-2 devs, orden libre por sitio)
| Sem | Foco | Salida verificable |
|---|---|---|
| 1 | Repo+Docker+CI, migs 001-008, Spatie teams, `TenantContext/Scope`, auth, `TenantIsolationTest`, Scribe | `pest` verde, OpenAPI visible |
| 2 | Migs 009-020, CRUD catalog+media (metadata + subida al front), `front-admin` login+switcher+CRUD | demo JyM cargada a mano |
| 3 | Learning + CRM + CORS por dominio + throttle + Sentry + `/healthz` | Biotek/Sport demo funcionales |
| 4 | Fronts públicos Astro (JyM, Casa Ángel) + hardening (gitleaks, rate-limit, backups MySQL) + go-live | 5 dominios en prod |

## 7. Bootstrap (ejecutar en orden)
```bash
composer create-project laravel/laravel backend && cd backend
composer require spatie/laravel-permission laravel/sanctum dedoc/scramble --dev larastan/larastan pestphp/pest
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
# teams: config/permission.php 'teams' => true
php artisan make:middleware ResolveTenant
php artisan make:trait Traits/BelongsToTenant  # + TenantScope global
php artisan make:test TenantIsolationTest --pest
```
Seeders iniciales: `TenantSeeder` (5 slugs: jym, casa-angel, biotek, sport-bogota, fotoaleph + dominios), `RolePermissionSeeder`, `DemoCatalogSeeder`.

## 8. Riesgos residuales (sin datos legacy igual aplica)
1. URLs/SEO nuevos → definir slugs finales antes de publicar (no habrá 301s porque no hay URLs viejas que respetar, pero sí consistencia).
2. Fotos: nombres UUID no predecibles + `noindex` en galerías de eventos + backup por hosting (cada front es isla: sin dedup global). Prohibido IDs secuenciales (`/media/123.jpg`).
3. Uploads: el coordinador sube al admin → el admin guarda el binario según `disk`: `front` = SFTP/API al hosting de ese site; `backend-private` = disco local del backend. El backend nunca sirve más que originales Casa Ángel.
