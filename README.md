# Status Link

Base administrativa de un SaaS multitenant para notarías, construida con Laravel 13, MySQL, Laravel Sanctum y Spatie Laravel Permission.

## Alcance actual

Esta fase contiene únicamente usuarios, notarías, planes, suscripciones históricas, pagos manuales, roles y permisos. No incluye módulos operativos, automatizaciones, notificaciones ni frontend administrativo.

La separación tenant usa una sola base de datos y `users.notary_id`. Los superadministradores pueden ser globales (`notary_id = null`); los administradores y usuarios de notaría quedan asociados a una notaría. Las policies y `UserTenantService` impiden administrar usuarios de otro tenant.

## Estructura

- Modelos `Notary`, `Plan`, `Subscription`, `Payment` y `User` en `app/Models`.
- Servicios de dominio en `app/Domain` para features/límites, aislamiento de usuarios, suscripciones y pagos manuales.
- Policies en `app/Policies` y middleware de cuenta, notaría y feature activos en `app/Http/Middleware`.
- Seeders de roles/permisos, planes iniciales, super admin y notaría demo.
- API versionada: `GET /api/v1/me`, protegida con Sanctum.

## Instalación y verificación

```bash
composer install
php artisan migrate:fresh --seed
php artisan test
```

Credenciales locales:

- `admin@status-link.local` / `password`
- `notaria@status-link.local` / `password`

Las claves JSON `features` y `limits` solo configuran capacidades futuras; no implementan esos módulos.
