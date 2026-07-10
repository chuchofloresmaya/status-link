# Status Link

Base administrativa multitenant para un SaaS de notarías, construida con Laravel 13, MySQL, Blade, Tailwind CSS 4, Laravel Sanctum y Spatie Laravel Permission.

## Panel administrativo

El panel incluye un layout responsive con sidebar por rol, topbar, dashboards segmentados y CRUD web para la base administrativa:

- Super admin: notarías, planes, suscripciones, pagos manuales y usuarios.
- Admin de notaría: usuarios limitados a su tenant y configuración propia.
- Usuario de notaría: dashboard básico.

Las rutas administrativas usan autenticación, validación de usuario/notaría activos, middleware de rol, policies y comprobaciones tenant en servidor. No existe registro público.

## Rutas principales

- `/`: redirige a login o dashboard según la sesión.
- `/login`: acceso web.
- `/dashboard`: dashboard según rol.
- `/admin/notaries`, `/admin/plans`, `/admin/subscriptions`, `/admin/payments`, `/admin/users`: administración global.
- `/app/users`: usuarios de la notaría autenticada.
- `/app/settings`: configuración permitida de la notaría autenticada.
- `/api/v1/me`: identidad autenticada mediante Sanctum.

## Roles

- `super_admin`: acceso administrativo global.
- `notary_admin`: administración de usuarios y configuración de su notaría.
- `notary_user`: acceso básico sin funciones administrativas.

## Planes comerciales

Status Link incluye cinco planes ordenados para presentación comercial:

- **Free / Gratuito:** $0 mensuales, para comenzar.
- **Basic / Básico:** $349 mensuales; promoción de $600 por 3 meses.
- **Professional / Profesional:** $799 mensuales; promoción de $1,290 por 3 meses. Es el plan destacado.
- **Premium:** $1,490 mensuales; promoción de $2,700 por 3 meses.
- **Corporate / Corporativo:** requiere cotización personalizada.

La información de cada plan se separa de esta forma:

- `marketing_features`: textos legibles que se presentan al cliente en la interfaz comercial.
- `features`: banderas técnicas que indican capacidades habilitadas; no implementan por sí mismas esas capacidades.
- `limits`: cantidades, frecuencias y valores máximos que consumen los servicios del dominio.
- `monthly_price`: precio mensual normal y fuente del campo compatible `price`.
- `promotional_price`: total de la promoción durante el número de meses indicado en `promotional_months`.
- `requires_quote`: indica que el precio debe acordarse comercialmente en lugar de mostrarse como tarifa fija.

## Credenciales demo

- Super admin: `admin@status-link.local` / `password`
- Admin de notaría: `notaria@status-link.local` / `password`

Estas credenciales son exclusivamente para desarrollo local.

## Instalación y verificación

```bash
composer install
npm install
npm run build
php artisan migrate --seed
php artisan test
vendor/bin/pint
```

Los valores JSON de features y límites preparan capacidades futuras; esta fase no implementa módulos operativos, automatizaciones, notificaciones ni pagos reales.
