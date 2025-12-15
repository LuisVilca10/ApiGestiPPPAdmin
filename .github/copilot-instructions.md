# Copilot instructions for ApiGestPPP

This repository is a Laravel API project. Below are concise, actionable notes to help AI coding agents be productive quickly.

## Big picture
- **Framework:** Laravel (app/, routes/, config/, migrations/) — API-focused controllers under `app/Http/Controllers/Api`.
- **Auth model:** JWT-based API auth using `php-open-source-saver/jwt-auth` (see `config/jwt.php`) — controllers use `auth:api` guard and expect a Bearer token.
- **Permissions:** `spatie/laravel-permission` is used for roles/permissions (see `app/Traits/RolePermissions.php` and middleware usage in `routes/api.php`).

## Key files and patterns (examples)
- Routes: [routes/api.php](routes/api.php) — route groups use `middleware('auth:api')` and `role`/`permission` middleware.
- Auth controller: [app/Http/Controllers/Api/Auth/AuthController.php](app/Http/Controllers/Api/Auth/AuthController.php) — uses `ApiResponseTrait`, `TokenHelper`, `ValidatorTrait` and returns standardized `successResponse`/`error` JSON.
- Traits: `app/Traits/ApiResponseTrait.php`, `RolePermissions.php`, `TokenHelper.php` — follow project conventions for responses and role assignment.
- Storage/uploads: `AuthController::uploadPhoto` stores files on the `public` disk (use `storage:link` when running locally).

## Coding conventions and expectations
- All API responses use the `ApiResponseTrait` format: `{ status, message, data }` for success and `{ status, message }`/`errors` for failures.
- Prefer existing traits for shared logic instead of duplicating behavior (validation, token handling, role assignment).
- Routes are grouped by prefix and middleware in `routes/api.php`; add new endpoints under the appropriate group and follow permission checks already used.

## Developer workflows / commands
- Install & prepare environment:
  - `composer install`
  - copy `.env.example` → `.env` (composer scripts may create this automatically)
  - `php artisan key:generate`
  - `php artisan jwt:secret` (set `JWT_SECRET` in `.env`)
  - `php artisan migrate`
  - `php artisan storage:link` (for uploaded images)
- Run dev environment (parallel tasks available via composer):
  - `composer run dev` (runs `php artisan serve`, queue listener, logs pail, and `npm run dev` per `composer.json`)
- Tests and QA:
  - `composer test` or `php artisan test`
  - Formatting: `composer run-script` hooks include `pint`/`pail` (see `composer.json` require-dev)

## Integration & external dependencies
- JWT: `php-open-source-saver/jwt-auth` — tokens created and parsed in controllers (see `AuthController` and `config/jwt.php`).
- Roles & permissions: `spatie/laravel-permission` — roles/permissions created/checked via `RolePermissions` trait and middleware strings such as `role:Admin|Estudiante`.
- PDF generation: `barryvdh/laravel-dompdf` present in `composer.json`.

## Practical examples to follow
- Add a protected endpoint: use `Route::middleware('auth:api')->group(...)` and return responses via `successResponse()`.
- Check permissions: add `->middleware('permission:editar_roles')` where appropriate (see `routes/api.php#role` routes).

## What not to assume
- There are multiple token helpers (see `app/Traits/TokenHelper.php` references to Passport). Prefer the JWT patterns actually used in controllers unless a code change standardizes Passport instead.
- Environment configuration values (JWT keys, TTL) live in `.env` and `config/jwt.php` — do not hardcode secrets.

If anything here is unclear or you want more detail in a specific area (database schema, a controller family, or tests), tell me which part and I will expand.
