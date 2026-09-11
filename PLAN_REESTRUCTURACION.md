# Plan de Reestructuración — Plataforma Aleph (Single-DB + `tenant_id`)

> **Estrategia:** Greenfield controlado + Strangler. Repo nuevo `aleph-platform`, repo actual `fotoaleph` queda en modo mantenimiento hasta el cutover por sitio. **Nada de big-bang.**
> **Fecha:** 2026-09-11

---

## 1. Punto de partida (lo que hay que preservar sí o sí)

| Micrositio vivo | Datos que no se pueden perder | Particularidad |
|---|---|---|
| Vidrios y Estructuras JyM | `categorias, grupos, proyectos, multimedia, multimedia_proyecto, materiales(morph)` | Catálogo + niveles (`nivel` en multimedia) + vitrina curada |
| Casa Ángel Eventos | `ocasiones, tematicas, colores, eventos, muestrarios(+etiquetas), multimedia, evento_multimedia(cantidad)` + `legacy_casa_angel` MySQL | Doble concepto: eventos realizados vs muestrario vendible; `cantidad` por foto |
| Biotek | `estudiantes, talleres, preguntas/opciones/intentos/respuestas, carnets, estudiantes_talleres` | Es un mini-LMS/evaluador, no un catálogo |
| Sport Bogotá | `estudiantes(categoria,nombres,apellidos), multimedia` | Registro simple + descargas |
| Transversal | `tenants, sitios, users, tenant_user, pqr, cotizaciones, mensajes, redes_sociales(+types), direcciones, telefonos, vitrinas(+items)` | CRM + vitrina global + contacto |

**Conclusión:** no todo es "catálogo". Hay 3 dominios distintos: **(a) Catálogo/Vitrina** (JyM, Casa Ángel), **(b) Formación/Registro** (Biotek, Sport), **(c) CRM/Contacto** (PQR, cotización, mensaje). El error del diseño actual fue meterlos en DBs separadas por cliente en vez de separarlos por **dominio con `tenant_id`**.

---

## 2. Decisión tecnológica propuesta

### 2.1 Stack recomendado (y por qué)

| Capa | Elección | Alternativa descartada y motivo |
|---|---|---|
| Backend | **Laravel 12 + PHP 8.4, API-first** (`/api/v1`), Sanctum tokens + `spatie/laravel-permission` | Fortify views + Inertia acoplado: te obliga a desplegar front+back juntos. Se jubila |
| DB | **PostgreSQL 16** (1 sola DB, `tenant_id` + FKs; RLS en fase 2) | 5× MySQL actuales: costo operativo ×5, sin joins. SQLite solo para tests |
| Media | **Una tabla `media` + disco S3-compatible** (MinIO local, R2/S3 prod) + `spatie/laravel-medialibrary` o tabla propia | 5 tablas `multimedia` idénticas con columnas divergentes (`nivel` solo en JyM, `cantidad` solo en pivot de Casa Ángel) |
| Front | **Desacoplado por sitio:** Astro (JyM, Casa Ángel — SEO) + Vue/Nuxt o Blade liviano (Biotek, Sport — apps privadas) | Un solo SPA Inertia para todos: mezcla SEO público con dashboard privado |
| AuthZ | `spatie/laravel-permission`: `roles(admin,coordinador,cliente)` + `permissions` **con `team_id = tenant_id`** (`teams` feature) | String `role` en `users`: no permite "soy admin en JyM pero cliente en Casa Ángel" |
| Contratos API | **Scribe o Scramble (OpenAPI)** + Pest + PHPStan lvl 8 + Pint en CI | Sin contrato hoy: front y back se rompen en silencio |
| Infra | **Docker Compose (api, pgsql, redis, minio, queue)** + GitHub Actions (migrate --dry, seed demo, `TenantIsolationTest`) + Sentry + `/healthz` | `GET /aguacate` + `.env` versionado: prohibido en el nuevo repo (gitleaks en CI) |

### 2.2 Repo nuevo, no refactor in-place

```
aleph-platform/               # MONOREPO
├── backend/                  # Laravel API (único deploy)
│   └── app/Modules/{Core,Catalog,Learning,CRM}/
├── front-jym/                # Astro, dominio vidriosyestructurasjym.com
├── front-casa-angel/         # Astro, eventoscasaangel.com
├── front-biotek/             # Vue/Nuxt privado
├── front-sport/              # Vue/Nuxt privado
├── front-admin/              # Vue/Nuxt, admin.dinamycode.com
└── infra/docker-compose.yml
```

`fotoaleph` se congela: solo bugfixes críticos + exportadores. Todo lo nuevo va a `aleph-platform`.

---

## 3. Nuevo modelo de datos (single-DB, desde cero)

### 3.1 Principios
1. Toda tabla de negocio lleva `tenant_id NOT NULL FK → tenants.id`. Sin excepción.
2. Nada de `$connection` por modelo. Una sola conexión `pgsql`.
3. `tenants`: `id, slug UNIQUE, name, domain UNIQUE, status(draft|active|suspended), settings JSONB`.
4. Usuarios globales, **roles por tenant**: `model_has_roles(team_id=tenant_id)` vía Spatie teams. Adiós columna `users.role`.
5. Media unificada (una tabla, no cinco).

### 3.2 Esquema objetivo (DDL lógico)

```sql
-- CORE
tenants(id, slug UNIQUE, name, domain UNIQUE, status, settings JSONB, timestamps)
sites(id, tenant_id FK CASCADE, name, slug, description, url, starts_on, ends_on, status)
users(id, name, email UNIQUE, password, ...)   -- sin role, sin tenant
-- Spatie: roles, permissions, model_has_roles(team tenant_id), team pivot

-- MEDIA UNIFICADA (reemplaza Jy/Ca/Bio/Sb/Multimedia ×5)
media(id, tenant_id FK, uuid UNIQUE, disk, path, preview_path,
      type(image|video|doc), mime, width, height, alt, level INT DEFAULT 0, meta JSONB)
mediables(id, media_id FK CASCADE, mediable_type, mediable_id, tenant_id FK, sort, meta JSONB)
-- meta absorbe 'cantidad' de Casa Ángel y 'aspect_ratio' divergente

-- CATÁLOGO (unifica Proyecto/Evento/Muestrario/Ocasión/Temática/Color/Categoría/Grupo)
taxonomies(id, tenant_id FK, kind(category|group|occasion|theme|color|tag), name, slug, description, color_hex, level INT)
catalog_items(id, tenant_id FK, kind(project|event|sample), slug UNIQUE por tenant,
              title, excerpt, body, code, location, happened_on, delivered_on,
              status(draft|published|archived), featured BOOL, level INT, meta JSONB)
catalog_item_taxonomy(item_id, taxonomy_id)     -- reemplaza categoria_id/grupo_id/ocasion_id/...
-- evento_multimedia(cantidad) → mediables.meta->cantidad

-- FORMACIÓN (unifica Biotek + Sport, Sport es subconjunto)
people(id, tenant_id FK, kind(student|lead|contact), first_names, last_names,
       doc_id UNIQUE por tenant, category, meta JSONB)   -- absorbe ambos 'estudiantes'
courses(id, tenant_id FK, code UNIQUE por tenant, title, held_on, duration_s)  -- 'talleres'
enrollments(person_id, course_id, status, credential_no, issued_on, expires_on) -- carnets+talleres pivot
quizzes: questions(id, tenant_id FK, course_id NULL, body, kind, level)
         options(id, question_id FK, body, is_correct)
         attempts(id, course_id FK, person_id FK, nro, score NULL, finished_at NULL)
         answers(id, attempt_id FK, question_id FK, option_id FK)

-- CRM (ya existe, solo gana tenant_id)
pqrs(id, tenant_id FK, user_id NULL, subject, body, status, ...)
quotes(id, tenant_id FK, user_id NULL, ...)/cotizaciones
messages(id, tenant_id FK, name, email, phone, body, read_at NULL, ...)
social_network_types(id, name, icon)  -- global, sin tenant
social_links(id, tenant_id FK NULL, linkable_type, linkable_id, type_id FK, url)
addresses/phones(id, tenant_id FK NULL, addressable/phoneable morph, ...)
```

### 3.3 Qué se elimina / fusiona (y por qué nadie lo va a extrañar)
- `JymCategoria/JymGrupo/Grupo/Categoria` (4 clases para 2 tablas `categorias/grupos`) → `taxonomies(kind=...)`.
- `Jy/Ca/Bio/SbMultimedia + Multimedia` (5 clases, 1 tabla `multimedia` ×5 DBs) → `media + mediables`.
- `Proyecto/Evento/Muestra` → `catalog_items(kind=...)`. JyM usa `kind=project`, Casa Ángel `kind=event|sample`. Mismo CRUD, misma vitrina.
- `BiotekEstudiante/Estudiante` → `people(kind=student)`. Sport solo usa 4 columnas; Biotek usa el resto.
- `Vitrina/VitrinaItem` (sincronizador dual-write) → **vista, no tabla**: `GET /catalog?featured=1` o tabla materializada si hace falta performance. Se elimina `TenantCatalogVitrinaSynchronizer` y las columnas `source_*`.
- `tenant_user` artesanal → Spatie `model_has_roles` con teams.
- `tenants.database_connection` → eliminado. Añadir tenant = `INSERT INTO tenants`.

---

## 4. Arquitectura backend (monolito modular)

```
app/Modules/Core/{Models/Tenant,Site,User,Media,Http,Policies,TenantScope,TenantContext}
app/Modules/Catalog/{Models/CatalogItem,Taxonomy,Http/Resources,Policies}
app/Modules/Learning/{Models/Person,Course,Enrollment,Question,...}
app/Modules/CRM/{Models/Pqr,Quote,Message,...}
routes: /api/v1/t/{tenant:slug}/catalog, /.../people, /.../pqrs, /api/v1/admin/tenants
middleware: ResolveTenant(slug|dominio) → TenantContext::set() → TenantScope (global WHERE tenant_id)
auth: Sanctum abilities ["tenant:{id}:catalog:write", ...]; policies por módulo
```

Reglas inviolables (con test que las hace cumplir):
- R1: ningún `WHERE` de negocio sin `tenant_id` (forzado por `TenantScope` + `TenantIsolationTest`).
- R2: ningún endpoint de catálogo sin paginación (`cursorPaginate`) ni sin `throttle`.
- R3: media siempre por UUID firmada (`/media/{uuid}?s=...`), nunca path predecible.
- R4: front nunca decide permisos; solo refleja `meta.can` que manda la API.

---

## 5. Migración desde los micrositios vivos (Strangler, cero downtime)

**Fase 0 — Congelar y medir (semana 1).** `fotoaleph` en `maintenance:except` solo lectura para cambios estructurales. Export inventario: conteos por tabla/DB + dump de `storage/` (fotos) con checksums. Definir slugs canónicos: `jym, casa-angel, biotek, sport-bogota, fotoaleph`.

**Fase 1 — Esqueleto verde (semanas 2-3).** Nuevo repo + Docker + CI + migraciones del §3 + seeders demo + `TenantIsolationTest` en rojo/verde + API `tenants/sites/auth` + front-admin mínimo (login + switcher de tenant).

**Fase 2 — ETL por tenant (semanas 4-6), orden sugerido: Sport → JyM → Casa Ángel → Biotek** (de simple a complejo; Biotek al final por ser LMS).
- Script por tenant `backend/database/etl/{sport,jym,casa_angel,biotek}.php`: lee MySQL legacy (solo-lectura), mapea IDs viejos→nuevos (`legacy_map` JSONB para rollback), copia archivos a `media/` con nuevo UUID, reescribe URLs.
- Validación: conteos origen=destino, spot-check visual de 20 fichas, verificación SEO (slugs viejos → 301 en `redirects` table).
- Dual-run 1 semana por sitio: el sitio viejo sigue sirviendo; un job nocturno re-sincroniza deltas. Cutover por DNS cuando el diff es 0.

**Fase 3 — Cutover por sitio (semanas 7-9).** Ventana nocturna por dominio: backup, ` artisan etl:freeze`, DNS a la nueva plataforma, humo (home, ficha, formulario PQR/cotización, login), rollback = revertir DNS (los IDs legacy permiten volver). Dar de baja la DB MySQL del tenant solo 30 días después.

**Fase 4 — Jubilación (semana 10+).** Apagar `fotoaleph`, archivar repo con tag `legacy-eol`, borrar credenciales viejas, activar RLS Postgres + backups por tenant (`pg_dump --where tenant_id` o esquemas lógicos).

---

## 6. Plan por semanas (equipo 1-2 devs)

| Sem | Entregable | Criterio de salida |
|---|---|---|
| 1 | Repo + Docker + modelo §3 migrado + inventario ETL | `migrate:fresh --seed` verde en CI |
| 2-3 | Auth+tenants+media+catalog API + `TenantIsolationTest` | Scribe publica OpenAPI; front-admin lista tenants |
| 4 | ETL Sport + front-sport privado | Cutover sport-bogota sin 404s |
| 5-6 | ETL JyM + front-jym (Astro) | Lighthouse >90, 301s verificados |
| 7 | ETL Casa Ángel (eventos+muestrarios) | `cantidad` y etiquetas preservadas, test visual OK |
| 8 | ETL Biotek (personas, talleres, intentos) | Notas/historial cuadran al 100% |
| 9 | CRM (pqr/cotización/mensaje) + CORS por tenant + Sentry | Formularios de los 4 sitios escriben en la nueva DB |
| 10 | EOL fotoaleph | 30 días sin rollback → archivar |

---

## 7. Riesgos top y mitigación

1. **Divergencia de `multimedia`** (columnas distintas por DB) → `media.meta JSONB` + script que normaliza `nivel/cantidad/aspect_ratio`; lo no mapeable va a `meta` sin pérdida.
2. **Slugs/SEO rotos** → tabla `redirects(old_path → new_path, tenant_id)` + test de 301s top-50 URLs por sitio (Analytics/Search Console).
3. **Fotos huérfanas o duplicadas** → dedup por hash SHA256 en ETL; `media.uuid` nuevo, se conserva `legacy_map{db,table,id,url}`.
4. **Biotek no es catálogo** → no forzarlo al modelo `catalog_items`; va a `Learning` tal cual. Intentar unificarlo sería el segundo error arquitectónico.
5. **Tentación de "ya que estamos, reescribo los fronts perfecto"** → fronts v1 son clon visual 1:1, solo cambian de API. Rediseño en fase posterior.

---

## 8. Decisiones cerradas (2026-09-11)

1. **DB: MySQL** (por hosting). Se usa `JSON` (no JSONB), sin RLS → aislamiento solo por `TenantScope` + `TenantIsolationTest`.
2. **AuthZ: superadmin global + coordinador por tenant** vía `spatie/laravel-permission` con `teams` (`team_id = tenant_id`). `Gate::before`: superadmin lo puede todo.
3. **Cutover: sin relevancia — los 4 sitios se re-publican desde cero.** Se elimina el Strangler/dual-run: no hay ETL ni sincronización delta.
4. **Dominios: sí.** Se mantienen los 4 dominios + nuevo `admin.dinamycode.com`.
5. **Datos históricos: NO se migran.** Sin `legacy_map`, sin importación de MySQL legacy. Solo seeders demo + carga manual inicial.

> Efecto: fases 2-3 del §5 quedan obsoletas. Ver `PLAN_EJECUCION_FRESCA.md` (build directo 4 semanas).
