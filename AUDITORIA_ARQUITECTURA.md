# Auditoría de Arquitectura — Backend Multi-sitio `fotoaleph`

> **Fecha:** 2026-09-11 · **Rol:** arquitecto de software externo
> **Alcance:** `app/`, `routes/`, `config/`, `database/`, `bootstrap/`, `tests/`, `.env(.example)`
> **Objetivo declarado:** ser el backend único de distintos sitios web (JyM Vidrios y Estructuras, Casa Ángel Eventos, Biotek, Sport Bogotá + sitios genéricos vía `tenants`/`sitios`/`vitrinas`).

---

## 1. Resumen ejecutivo

El proyecto es hoy un **monolito Laravel 13 + Fortify + Sanctum + Inertia/Vue** con una **multi-tenancy casera de 5 conexiones fijas** (`tenant_central`, `tenant_jym`, `tenant_casa_angel`, `tenant_biotek`, `tenant_sport_bogota` + `legacy_casa_angel`), **sin paquete de tenancy** (`stancl/tenancy` ausente en `composer.json`).

El concepto de "tenant actual" **no funciona**: el middleware `ResolveTenantConnection` está registrado como alias pero **nunca se aplica a ninguna ruta**, y el `TenantConnectionResolver` nunca conmuta la conexión de Eloquent (`DB::setDefaultConnection()`, `setConnection()` dinámico, etc. no existen). En la práctica cada modelo usa su `$connection` hardcodeado y cada controlador filtra por `abort_unless($tenant->databaseConnectionName() === 'tenant_x', 404)` y luego consulta **toda** la tabla de esa conexión sin `tenant_id`.

**Veredicto:** funciona como 4 backends pegados con cinta dentro de un mismo deploy, no como plataforma multi-sitio extensible. Añadir el 5.º sitio exige tocar `config/database.php`, modelos, migraciones, seeders, comandos `tenancy:*`, policies, gates y controladores. Hay además **2 vulnerabilidades críticas abiertas** (`GET /aguacate` ejecuta migrate+seed sin auth; `.env` con credenciales reales versionado), **1 bug que rompe API** (middleware `autorizado` inexistente), **1 modelo fantasma** (`Nivel` referenciado pero no existe) y **relaciones cross-DB imposibles en Eloquent**.

**Recomendación:** no escalar el diseño actual. Migrar a **una de las 2 arquitecturas objetivo** (§7): (A) **Single-DB + `tenant_id` + Modular Monolith** (recomendada para tu escala, 4-20 sitios pequeños) o (B) **DB-per-tenant con `stancl/tenancy`** (solo si necesitas aislamiento físico/auditoría por cliente). En paralelo aplicar los **quick wins de seguridad del §8** (< 1 día).

---

## 2. Arquitectura actual (reconstruida del código)

```
                    ┌──────────────────── Inertia / Vue (resources/) ────────────────────┐
                    │  Dashboard, tenants/proyectos|eventos, estudiantes, pqrs, settings │
                    └────────────────────────────────┬───────────────────────────────────┘
                                                     │ web.php (Fortify session + role:admin)
                                                     ▼
┌──────────────┐   ┌─────────────────────── LARAVEL MONOLITO ───────────────────────┐   ┌───────────────┐
│ Vue SPA /    │──▶│ api.php: /jym/* /casa-angel/* /mensajes /redes-sociales (mixto │──▶│ 5 MySQL (prod)│
│ sitios       │   │          público + auth:sanctum, sin prefijo :tenant)          │   │ 1 MySQL (dev) │
│ externos     │   │                                                                │   │ 0 sqlite      │
└──────────────┘   │ web.php: tenants/{tenant}/proyectos|eventos|biotek (Inertia)   │   └───────────────┘
                   │ middleware tenant.connection: DEFINIDO pero NUNCA USADO        │
                   │ TenantConnectionResolver: set/forget sin efecto real           │
                   └────────────────────────────────┬───────────────────────────────┘
                                                    │ $connection hardcodeado por modelo
                                                    ▼
                    central/default: User,Tenant,Sitio,Vitrina(+items),Multimedia,Categoria,Pqr,Cotizacion,Mensaje,...
                    tenant_jym: Proyecto,JymCategoria,JymGrupo,Grupo,JyMultimedia
                    tenant_casa_angel: Evento,Ocasion,Tematica,Color,Muestra,CaMultimedia
                    tenant_biotek: BiotekEstudiante,Taller,BioMultimedia
                    tenant_sport_bogota: Estudiante,SbMultimedia
```

**Flujo típico web** (`TenantProyectoController::authorizeTenant()` + `renderPage()`):

1. `GET tenants/{tenant}/proyectos` → `Tenant` por route-model binding (central).
2. `abort_unless($tenant->databaseConnectionName() === 'tenant_jym', 404)` + `Gate::authorize('manage-tenant', $tenant)`.
3. `Proyecto::query()->latest()->get()` — **toda** la conexión `tenant_jym`, sin filtrar por `$tenant`.
4. Al guardar, `TenantCatalogVitrinaSynchronizer` duplica a `Vitrina` central + `Multimedia::firstOrCreate` + `sync` con `source_type/source_id/source_connection`.

**Flujo típico API:** rutas planas (`GET /jym/proyectos`, `GET /casa-angel/eventos`) sin `{tenant}` ni middleware tenant. Existen `PublicTenant*Controller::byTenant()/bySite()` bien diseñados pero **sin ninguna `Route::` que los exponga** — código muerto.

---

## 3. Inventario verificado

| Capa | Archivos clave |
|---|---|
| HTTP web | `app/Http/Controllers/{Welcome,Dashboard,Auth,Tenant,TenantProyecto,TenantEvento,BiotekEstudiante,Estudiante,Categoria,Grupo,Sitio,User,Customer,Pqr,Cotizacion,Ocasion,SocialNetwork}Controller.php` + `Settings/{Profile,Security}Controller.php` |
| HTTP API | `app/Http/Controllers/Api/{JymCatalog,JymProyectoCotroller[TYPO],JymGroup,JymCategoria,CasaAngelEventCatalog,CasaAngelMuestrario,PublicTenantProject,PublicTenantEvent,PublicTenantVitrina,ManagedTenantVitrina,Mensaje}Controller.php` |
| Modelos (31) | `Tenant,User,Sitio,Vitrina,VitrinaItem,Multimedia,Proyecto,Evento,Categoria,Grupo,JymCategoria,JymGrupo,JyMultimedia,CaMultimedia,BioMultimedia,SbMultimedia,BiotekEstudiante,Estudiante,Taller,Ocasion,Tematica,Color,Muestra,Direccion,Telefono,SocialNetwork,SocialNetworkType,Pqr,Cotizacion,Mensaje` — **falta `Nivel`** |
| Soporte tenancy | `app/Support/Tenants/{TenantConnectionResolver,TenantCatalogVitrinaSynchronizer}.php`, `app/Support/{Vitrinas/VitrinaInteractionTracker,Api/IntegerCounterMutation}.php` |
| Pipelines | `app/Pipelines/Vitrinas/{FiltrarPorGrupo,FiltrarPorCategoria,OrdenarPorFechaYNivel}.php`, `app/Pipelines/Estudiantes/{FiltrarPorNombre,FiltrarPorCategoria,OrdenarPorCampo}.php` |
| AuthZ | `app/Http/Middleware/{ResolveTenantConnection,CheckRole}.php`, `app/Policies/` (10), `Gates` en `app/Providers/AppServiceProvider.php:60-91` |
| Rutas | `routes/web.php` (78 lín.), `routes/api.php` (59 lín.), `routes/settings.php`, `routes/console.php` (20 comandos `tenancy:*`) |
| Config | `config/database.php` (5 conexiones tenant + `legacy_casa_angel`), `config/cors.php` (solo `api/*`), `config/auth.php` (solo guard `web`), `config/sanctum.php` (defaults) |
| DB | `database/migrations/central/` (13), `tenant_jym/` (1), `tenant_casa_angel/` (2), `tenant_biotek/` (1), `tenant_sport_bogota/` (1), **1 huérfana** `2026_08_30_..._create_mensajes_table.php`; seeders central+jym+casa+biotek+sport+vitrinas |
| Tests | `tests/Feature` (auth, dashboard, cors, mensajes, vitrinas), `tests/Unit/{TenantDatabaseConnection,VitrinaFilterPipeline}Test.php`, `phpunit.xml` usa sqlite `:memory:` |

---

## 4. Hallazgos CRÍTICOS 🔴

### C1. Endpoint `GET /aguacate` ejecuta migraciones y seeders sin autenticación
`routes/web.php:22-40` itera 11 comandos `tenancy:migrate-*` / `tenancy:seed-*` y devuelve `'Migrated'`. Sin `auth`, sin firma (`signed`), sin `throttle`, sin restricción por entorno. Cualquiera que descubra la URL puede **reescribir datos de los 5 tenants** (DoS + destrucción de datos). Es además un vector de enumeración (confirma nombres de tenants).
**Fix inmediato:** borrar la ruta. Si se necesita bootstrap remoto, usar comando artisan via SSH/CI, o ruta `POST` con `auth + role:admin + signed + throttle + App::isProduction() abort`.

### C2. Secretos reales versionados en `.env`
`.env` (trackeado en el repo inspeccionado) contiene passwords literales de MySQL (`Laravel_2024!`, `Vidrios&Estructuras`, `Eventos&Catering`, `Sport*Bogota8`, `MAIL_PASSWORD=2562622`). `.gitignore` estándar de Laravel excluye `.env`, pero el archivo está presente en el working tree entregado — si alguna vez se hizo `git add -f` o se distribuyó el zip, las credenciales están comprometidas.
**Fix:** rotar TODAS las credenciales, `git rm --cached .env`, verificar `git log --all -- .env`, mover a `.env.production` en vault/CI secrets, activar 2FA en el hosting MySQL.

### C3. Middleware `autorizado` inexistente rompe la API protegida de mensajes
`routes/api.php:33` exige `['auth:sanctum','role:admin','autorizado']` pero `bootstrap/app.php:33-36` solo registra aliases `role` y `tenant.connection`. Resultado: `GET/PATCH/DELETE /api/mensajes*` → **500 `Target class [autorizado] does not exist`** siempre. Verificado: ningún `grep autorizado` en `app/` define ese middleware.
**Fix:** registrar el alias faltante o retirar `autorizado` de la ruta (decidir si la intención era `Gate`/policy).

### C4. Relaciones cross-database que Eloquent no puede resolver
- `User(central/default) ↔ Evento(tenant_casa_angel)` vía `belongsToMany(Evento::class)` (`app/Models/User.php`) — join imposible entre conexiones.
- `Proyecto(tenant_jym) ↔ Multimedia(default)` y `Evento ↔ Multimedia` — se parchea con `Multimedia::on('tenant_x')` ad-hoc por controlador, lo que **duplica filas de `multimedia` por cada DB** y rompe `firstOrCreate` global.
- `Vitrina(central) → Proyecto/Evento` solo vía columnas `source_type/source_id/source_connection` en `vitrina_items` + `TenantCatalogVitrinaSynchronizer` — sin FK, sin integridad referencial.
- Consecuencia medible: `Vitrina::filtrar()` hace `::get()` completo + `Pipeline` en memoria (`app/Models/Vitrina.php:84-103`): **O(N) memoria** por request, sin paginación SQL.

### C5. Modelo `Nivel` inexistente, referenciado en 3 puntos calientes
`grep Nivel`: `app/Models/Proyecto.php:40` (`niveles(): MorphMany(Nivel::class)`), `app/Models/Vitrina.php:43` (`nivel(): MorphOne`), `app/Support/Vitrinas/VitrinaInteractionTracker.php:7,31`. `app/Models/Nivel.php` **no existe** (glob vacío). Cualquier request que toque `niveles/nivel/record()` → `Class "App\Models\Nivel" not found` 500.
Relacionado: `TenantEventoController` usa `$evento->ocasion|tematica|color|publicar_en_vitrina` que `Evento` no define como relación/fillable/cast.

---

## 5. Hallazgos ALTOS 🟠

### H1. Tenancy decorativa: el resolver nunca conmuta conexión
`TenantConnectionResolver::connectionFor()` no tiene **ningún caller** fuera del middleware; el middleware hace `setCurrentTenant()` y luego `forgetCurrentTenant()` en `finally` sin que nadie lea el valor en el medio. No hay `DB::purge/setDefaultConnection`, ni `Model::resolveConnectionUsing`, ni scope global por tenant. El "tenant actual" es un setter sin lector. Evidencia: `bootstrap/app.php` solo crea el alias `tenant.connection`, cero `->middleware()` / `Route::middleware('tenant.connection')`.

### H2. Escalabilidad nula: añadir un sitio = editar ~8 lugares
Nuevo tenant hoy exige: (1) bloque en `config/database.php:156-209`, (2) vars `TENANT_X_*` en `.env`, (3) modelos con `$connection='tenant_x'`, (4) carpeta `migrations/tenant_x/`, (5) 3 comandos en `routes/console.php`, (6) seeder, (7) `abort_unless(...==='tenant_x')` en un controlador nuevo, (8) policy/gate. `tenants.database_connection` es string libre sin `CHECK`/enum/FK → un typo (`tenant_jim`) pasa validación y explota en runtime.

### H3. Sin aislamiento por tenant dentro de cada conexión
`proyectos`, `eventos`, `estudiantes` **no tienen `tenant_id`**. `TenantProyectoController::renderPage()` y `TenantEventoController` listan todo lo de la conexión para cualquier `Tenant` que comparta `database_connection`. Dos filas `Tenant` apuntando a `tenant_jym` ven y editan los mismos proyectos (IDOR horizontal). El `manage-tenant` gate comprueba pertenencia al `Tenant`, no al **contenido**.

### H4. Doble fuente de verdad `.env` vs `.env.example`
`.env` real: todo MySQL, `DB_CONNECTION=mysql`, `TENANT_CENTRAL_DB_DATABASE=laravel` (= misma DB que `DB_DATABASE`). `.env.example`: `DB_CONNECTION=tenant_central` + 4× sqlite con `/absolute/path/to/...sqlite`. Ningún sqlite tenant existe en disco (solo `database/database.sqlite`). Resultado: **local, CI (`:memory:`) y prod hablan dialectos distintos**; validaciones como `Rule::unique('tenant_jym.proyectos')` (`TenantProyectoController`) fallan en sqlite/CI.

### H5. CORS incoherente + Sanctum a medias
`.env` define `CORS_ALLOWED_ORIGINS` **dos veces** (`:19` y `:94`); la segunda pisa a la primera y se pierden `dinamycode/eventoscasaangel/vidrios...`. `config/cors.php:21` solo cubre `api/*` (Inertia web queda fuera), `supports_credentials=false` pero Sanctum cookie-based lo necesita en `true` para el dashboard. Orígenes hardcodeados en `.env.example:54` mezclan 3 clientes distintos en una sola variable global — imposible emitir `Access-Control-Allow-Origin` por tenant.

### H6. Autorización inconsistente y contradictoria
- `CasaAngelEventCatalog@index` (ruta pública `GET /casa-angel/eventos`) llama `authorize('viewAny')` que exige `admin|coordinador-casa-angel|cliente` → la ruta pública **exige login**, contradicción.
- `PublicTenant*::byTenant/bySite` sí validan `abort_unless(databaseConnectionName()==='tenant_x')` pero están **muertos** (sin ruta).
- `Gate manage-sport-bogota-estudiantes` hardcodea el string `'Sport Bogota'` (`AppServiceProvider`) — renombrar el tenant rompe el gate silenciosamente.
- Roles como string (`admin|coordinador|cliente`) sin tabla/enum; `CheckRole` redirige a login en vez de 403 para no-autenticados en API (rompe clientes JSON).

### H7. Migración `mensajes` huérfana + `loadMigrationsFrom` parcial
`database/migrations/2026_08_30_..._create_mensajes_table.php` está fuera de `central/` y de `tenant_*/`, por lo que `tenancy:migrate-central --path=.../central` **no la corre**, pero `AppServiceProvider:32 loadMigrationsFrom(central)` + `migrate` sin `--path` sí la ve según entorno → esquema divergente entre `migrate` y `tenancy:migrate-central`. Mismo riesgo para futuros archivos en raíz de `migrations/`.

### H8. Tests que no prueban tenancy real
`phpunit.xml:26-28` usa sqlite `:memory:` + `DB_CONNECTION` default, pero las validaciones y los modelos apuntan a `tenant_jym.proyectos` (sintaxis MySQL `db.tabla`) → los tests de esos controladores solo pasan si se evita la validación o si se mockea. `TenantDatabaseConnectionTest` aserta nombres de conexión, no aislamiento de datos. No hay test de "tenant A no ve datos de tenant B".

---

## 6. Hallazgos MEDIOS / BAJOS 🟡

| # | Hallazgo | Evidencia |
|---|---|---|
| M1 | Typos que erosionan confianza | `JymProyectoCotroller` (controller+archivo), `Proyecto::imaganes()`, `JymCategoria` controlador colisiona con `JymCategoria` modelo, `sendMessage` valida `'phone'=>'email'` |
| M2 | Bug lógico en catálogo | `CasaAngelEventCatalog::index:25` llama `$user->eventos(fn...)` como si fuera builder; `eventos` es `BelongsToMany` ya resuelta como Collection → `BadMethodCallException` para usuarios logueados |
| M3 | `updateCantidad` ambiguo | `where('multimedia.id', ...)` sin join explícito con alias puede devolver `Column ambiguous` según driver |
| M4 | Seeders divergentes | `DatabaseSeeder` omite biotek/sport; `tenancy:reset-demo` sí los incluye; `TenantSeeder` hardcodea `syncWithoutDetaching([1,2,3,4])`; passwords con `bcrypt()` en vez de `Hash::` |
| M5 | `legacy_casa_angel` hardcoded | `config/database.php:211-220` con `database='legacy_casa_angel'` fijo + comando `MigrateLegacyEventos` sin documentar estrategia de corte |
| M6 | Sin rate-limit / paginación / API versioning | `api.php` sin `throttle:api`, sin `paginate()`, sin prefijo `/v1`; `JymCatalogController` devuelve colecciones completas |
| M7 | Observabilidad nula | `LOG_CHANNEL=stack/single`, sin request-id, sin Sentry/Telescope en prod, sin healthcheck de las 5 DBs (`/up` solo chequea default) |
| M8 | Frontend acoplado al monolito | Inertia + `vite` obligan a desplegar front+back juntos; imposible escalar o cachear por sitio (`fotoaleph` sirve todos los dominios desde un solo `APP_URL`) |

---

## 7. Alternativas de diseño

### Opción A — Single-DB + `tenant_id` + Monolito Modular (RECOMENDADA ⭐)
Una sola DB MySQL/Postgres. Toda tabla de negocio gana `tenant_id` FK → `tenants.id`. Un `BelongsToTenant` trait + global scope + `TenantContext` (del subdominio/header/`{tenant}`) aísla automáticamente. Módulos por dominio: `app/Modules/Jym/`, `CasaAngel/`, `Biotek/`, `SportBogota/`, `Core/` (vitrinas, mensajes, redes).
- ✅ Pros: 1 migración, 1 backup, joins reales, tests simples, añadir tenant = 1 fila, costo mínimo. Ideal para 4–20 sitios pequeños/medianos.
- ❌ Contras: aislamiento lógico (no físico); un bug en el scope expone datos (mitigable con tests de aislamiento + RLS de Postgres a futuro).
- Coste: medio (migración de datos con script por conexión).

### Opción B — DB-per-tenant con `stancl/tenancy` + central
Mantiene 1 DB por sitio pero con librería probada: identificación por dominio/subdominio, switching automático, migraciones/seeders/queue/cache/horizon por tenant, `tenants` table + `domains` table.
- ✅ Pros: aislamiento físico, backups/restauración por cliente, borrado GDPR trivial.
- ❌ Contras: N DBs que operar, migraciones × N, joins central↔tenant imposibles (vitrinas globales requieren sincronización/eventos), overkill para catálogos pequeños.
- Coste: medio-alto; solo compensa si los clientes exigen aislamiento contractual o volúmenes muy dispares.

### Opción C — API-first headless + frontends separados
Laravel solo API versionada (`/api/v1/:tenant/...`), Sanctum tokens + scopes por tenant; frontends (Vue/Astro por sitio) desplegados por separado (Vercel/Netlify) con CORS por tenant.
- ✅ Pros: desacopla deploys, escala front y back por separado, permite dominios `fotoaleph.com/jym` + `vidrios...com` limpios.
- ❌ Contras: más piezas (auth SSR, CORS, previews); no resuelve por sí sola el modelo de datos (combinar con A o B).
- Coste: bajo si se hace gradual (primero versionar API actual).

**Decisión propuesta:** **A + C gradual**: normalizar a single-DB con `tenant_id` (A) y en paralelo versionar la API y extraer frontends por sitio (C). Reservar B solo si un cliente enterprise lo exige por contrato.

---

## 8. Diseño objetivo propuesto (concreto)

```
app/Modules/{Core,Jym,CasaAngel,Biotek,Sport}/
  ├── Http/{Controllers,Requests,Resources}
  ├── Models/          ← todos con BelongsToTenant + tenant_id
  ├── Policies/
  ├── Services/        ← lógica (VitrinaSynchronizer → evento de dominio)
  └── routes.{web,api}.php  ← cargadas con prefijo /t/{tenant_slug}

DB única: tenants(id, slug UNIQUE, name, domain UNIQUE, status)
          proyectos/eventos/estudiantes/... → tenant_id FK CASCADE
          vitrinas → tenant_id (adiós source_connection)
          multimedia → tenant_id NULLABLE (global) o tabla por tenant
HTTP:  /api/v1/t/{tenant}/proyectos, /api/v1/t/{tenant}/eventos
       middleware ResolveTenant (por slug|dominio) → TenantContext::set()
       scope global TenantScope aplica WHERE tenant_id automáticamente
       Sanctum tokens con abilities ["tenant:{id}:read","tenant:{id}:write"]
CORS:  mapa por tenant en config/cors-tenants.php, no una env global
OBS:   request-id + Sentry + healthcheck /healthz chequea DB+queue+storage
```

Reglas inviolables:
1. Ningún `Model` con `$connection` hardcodeado (salvo `legacy` de solo-lectura durante migración).
2. Ninguna query de negocio sin scope de tenant (forzado por CI: test `TenantIsolationTest` + PHPStan rule).
3. Ninguna ruta `tenants/{tenant}` sin `ResolveTenant` + `authorize('manage-tenant')` + route-model binding por `slug`.
4. Ningún secreto en git (gitleaks en CI).

---

## 9. Roadmap priorizado

### Hoy (< 1 día) — seguridad
- [ ] Eliminar `GET /aguacate` (`routes/web.php:22-40`).
- [ ] Rotar credenciales filtradas + `git rm --cached .env` + auditar `git log -- .env` + gitleaks en CI.
- [ ] Corregir `autorizado` (registrar middleware o quitarlo de `routes/api.php:33`) — desbloquea mensajería admin.
- [ ] Unificar `CORS_ALLOWED_ORIGINS` y separar por entorno; `supports_credentials` coherente con Sanctum.
- [ ] Crear `app/Models/Nivel.php` o eliminar sus 3 referencias (desbloquea vitrinas/niveles).

### Corto (1–2 semanas) — corrección
- [ ] Mover `mensajes` migration a `central/`; alinear `DatabaseSeeder` ↔ `tenancy:reset-demo`; `Hash::make` en seeders.
- [ ] Montar `tenant.connection` en rutas `tenants/{tenant}/*` o eliminarlo si se adopta §8; exponer o borrar `PublicTenant*`/`ManagedTenantVitrina`.
- [ ] Añadir `tenant_id` a `proyectos/eventos/estudiantes` como primer paso a Opción A (backfill desde `database_connection`).
- [ ] `TenantIsolationTest`: "tenant A no ve/edita datos de tenant B" en cada módulo; `throttle:api` + `paginate()` en catálogos.
- [ ] Renombrar `JymProyectoCotroller→JymProyectoController`, `imaganes→imagenes`; separar controlador `JymCategoria` del modelo.

### Medio (1–2 meses) — plataforma
- [ ] Migrar a single-DB + `TenantContext` + global scopes (Opción A); script de consolidación de las 5 DBs.
- [ ] Versionar API `/api/v1/t/{tenant}/...` con Resources + OpenAPI (scribe); CORS por tenant; tokens con abilities.
- [ ] Extraer frontends por sitio (Inertia→API + Astro/Vue por dominio); deploys independientes.
- [ ] Observabilidad: Sentry, Telescope solo local, `/healthz` multi-DB, backups probados por tenant.

---

## 10. Anexo — cómo verificar cada hallazgo (repro)

```bash
# C1: ruta abierta
grep -n "aguacate" routes/web.php
# C3: middleware fantasma (vacío = no existe)
grep -rn "class.*Autorizado\|'autorizado'" app/ bootstrap/ ; grep -n "autorizado" routes/api.php
# C5: modelo fantasma (vacío = no existe)
ls app/Models/Nivel.php ; grep -rn "Nivel::class\|use App\\\\Models\\\\Nivel" app/
# H1: middleware nunca usado
grep -rn "tenant.connection" routes/ bootstrap/app.php
grep -rn "connectionFor\|getCurrentTenant" app/ | grep -v "TenantConnectionResolver.php"
# H2: conexiones fijas
grep -n "tenant_jym\|tenant_casa_angel\|tenant_biotek\|tenant_sport" config/database.php | head
# H3: sin tenant_id
grep -rn "tenant_id" database/migrations/tenant_jym database/migrations/tenant_casa_angel | head
# H4: divergencia env
diff <(grep TENANT .env) <(grep TENANT .env.example)
grep -n "CORS_ALLOWED_ORIGINS" .env
```

---

*Documento generado como auditoría estática (sin ejecutar migraciones). Siguiente paso sugerido: elegir Opción A o B en reunión de 30 min y abrir issues a partir del §9.*
