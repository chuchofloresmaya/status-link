# Status Link

Base administrativa multitenant para un SaaS de notarías, construida con Laravel 13, MySQL, Blade, Tailwind CSS 4, Laravel Sanctum y Spatie Laravel Permission.

## Panel administrativo

El panel incluye un layout responsive con sidebar por rol, topbar, dashboards segmentados y CRUD web para la base administrativa:

- Super admin: notarías, planes, suscripciones, pagos manuales y usuarios.
- Admin de notaría: usuarios limitados a su tenant y configuración propia.
- Usuario de notaría: dashboard básico.

## Configuración notarial y bancaria

La tabla `notaries` sigue representando al tenant. Dentro de cada tenant pueden existir varios perfiles notariales (`NotarialProfile`) para representar números de notaría, notarios firmantes, datos fiscales y logos distintos. Cada perfil puede tener cuentas bancarias propias, mientras que una cuenta sin perfil asociado funciona como cuenta general de la organización.

- Solo puede existir un perfil notarial predeterminado por tenant.
- Puede existir una cuenta general predeterminada y una cuenta predeterminada independiente por cada perfil.
- Las cuentas usan un `account_type` abierto. Inicialmente se ofrecen `general`, `honorarios` e `impuestos`, pero pueden guardarse categorías personalizadas.
- Cada combinación de notaría, perfil (o cuenta general) y tipo mantiene su propia cuenta predeterminada. Cambiar la cuenta de honorarios no altera la de impuestos.
- Al seleccionar un nuevo default se limpia el anterior dentro del mismo alcance.
- Al desactivar un default se elige otro recurso activo del mismo alcance cuando existe.
- Los perfiles y cuentas no se eliminan físicamente; se activan o desactivan.
- Los logos se guardan en `storage/app/public/notarial-profiles/logos` y su ruta relativa se registra en `logo_path`.

En una instalación nueva debe publicarse el enlace de storage:

```bash
php artisan storage:link
```

Esta estructura queda preparada para que una futura cotización seleccione perfil notarial y cuenta bancaria, pero no incluye ni implementa un módulo de cotizaciones.

Las rutas administrativas usan autenticación, validación de usuario/notaría activos, middleware de rol, policies y comprobaciones tenant en servidor. No existe registro público.

## Rutas principales

- `/`: redirige a login o dashboard según la sesión.
- `/login`: acceso web.
- `/dashboard`: dashboard según rol.
- `/admin/notaries`, `/admin/plans`, `/admin/subscriptions`, `/admin/payments`, `/admin/users`: administración global.
- `/app/users`: usuarios de la notaría autenticada.
- `/app/settings`: configuración permitida de la notaría autenticada.
- `/app/notarial-profiles`: perfiles notariales del tenant autenticado.
- `/app/bank-accounts`: cuentas bancarias del tenant autenticado.
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
