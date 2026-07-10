<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->plans() as $plan) {
            Plan::updateOrCreate(['slug' => $plan['slug']], $plan);
        }
    }

    private function plans(): array
    {
        $baseFeatures = [
            'email_alerts' => false, 'whatsapp_alerts' => false, 'rejection_ai_summary' => false,
            'basic_history' => false, 'subsane_alerts' => false, 'custom_frequency' => false,
            'multi_office' => false, 'integrations' => false, 'priority_support' => false,
        ];

        return [
            $this->plan('Free', 'free', 'Para comenzar', '🆓 Gratuito', 'Ideal para probar el sistema sin costo.', 0, null, null, null, 1,
                ['Hasta 20 volantes activos', 'Actualización automática 1 vez al día', 'Consulta manual cada 4 horas', 'Usuarios ilimitados', 'Sin alertas'],
                $baseFeatures, ['active_records' => 20, 'auto_refreshes_per_day' => 1, 'manual_refresh_cooldown_minutes' => 240, 'users' => null]),
            $this->plan('Basic', 'basic', 'Crecimiento', '💼 Básico', 'Para notarías que ya manejan un volumen constante.', 349, 600, 3, 200, 2,
                ['Hasta 150 volantes activos', 'Actualización automática 1 vez al día', 'Consulta manual cada 3 horas', 'Usuarios ilimitados', 'Historial básico'],
                array_merge($baseFeatures, ['basic_history' => true]), ['active_records' => 150, 'auto_refreshes_per_day' => 1, 'manual_refresh_cooldown_minutes' => 180, 'users' => null]),
            $this->plan('Professional', 'professional', 'Más recomendado', '🚀 Profesional', 'El plan ideal para operación diaria más seria y controlada.', 799, 1290, 3, 430, 3,
                ['Hasta 400 volantes activos', 'Actualización automática 1 vez al día', 'Consulta manual cada 2 horas', 'Usuarios ilimitados', 'Alertas por correo electrónico', 'Avisos de subsanes'],
                array_merge($baseFeatures, ['email_alerts' => true, 'basic_history' => true, 'subsane_alerts' => true]), ['active_records' => 400, 'auto_refreshes_per_day' => 1, 'manual_refresh_cooldown_minutes' => 120, 'users' => null], 'Mejor equilibrio', true),
            $this->plan('Premium', 'premium', 'Avanzado', '🔥 Premium', 'Para notarías con alto volumen y seguimiento intensivo.', 1490, 2700, 3, 900, 4,
                ['Hasta 1000 volantes activos', 'Actualización automática 2 veces al día', 'Consulta manual cada 30 minutos', 'Usuarios ilimitados', 'Alertas por correo', 'Alertas por WhatsApp', 'IA resume motivo del rechazo o suspensión', 'Incluye resumen con escritura, volumen, link y motivo'],
                array_merge($baseFeatures, ['email_alerts' => true, 'whatsapp_alerts' => true, 'rejection_ai_summary' => true, 'basic_history' => true, 'subsane_alerts' => true]), ['active_records' => 1000, 'auto_refreshes_per_day' => 2, 'manual_refresh_cooldown_minutes' => 30, 'users' => null, 'summary_fields' => ['escritura', 'volumen', 'link', 'motivo']]),
            $this->plan('Corporate', 'corporate', 'Empresarial', '🏛️ Corporativo', 'Diseñado para operación multi-oficina y necesidades especiales.', 0, null, null, null, 5,
                ['Más de 1000 volantes', 'Varias oficinas', 'Integraciones', 'Frecuencia personalizada', 'Soporte prioritario'],
                array_merge($baseFeatures, ['email_alerts' => true, 'whatsapp_alerts' => true, 'rejection_ai_summary' => true, 'basic_history' => true, 'subsane_alerts' => true, 'custom_frequency' => true, 'multi_office' => true, 'integrations' => true, 'priority_support' => true]), ['active_records' => null, 'auto_refreshes_per_day' => null, 'manual_refresh_cooldown_minutes' => null, 'users' => null], null, false, true),
        ];
    }

    private function plan(string $name, string $slug, string $category, string $badge, string $description, float $monthlyPrice, ?float $promotionalPrice, ?int $promotionalMonths, ?float $equivalentPrice, int $order, array $marketingFeatures, array $features, array $limits, ?string $secondaryLabel = null, bool $highlighted = false, bool $requiresQuote = false): array
    {
        return compact('name', 'slug') + [
            'category_label' => $category, 'badge_label' => $badge, 'secondary_label' => $secondaryLabel,
            'description' => $description, 'price' => $monthlyPrice, 'monthly_price' => $monthlyPrice,
            'promotional_price' => $promotionalPrice, 'promotional_months' => $promotionalMonths,
            'promotional_equivalent_monthly_price' => $equivalentPrice, 'billing_period' => 'monthly',
            'requires_quote' => $requiresQuote, 'display_order' => $order, 'marketing_features' => $marketingFeatures,
            'features' => $features, 'limits' => $limits, 'is_highlighted' => $highlighted, 'is_active' => true,
        ];
    }
}
