<x-app.layout title="Planes">
    <x-ui.page-header title="Planes" description="Oferta comercial y límites del servicio." :action="route('admin.plans.create')" />
    <x-ui.table>
        <thead class="bg-slate-50 text-left text-xs uppercase text-slate-500"><tr>
            @foreach (['Orden', 'Badge', 'Nombre', 'Precio mensual', 'Promoción', 'Cotización', 'Estado', 'Acciones'] as $heading)<th class="px-4 py-3">{{ $heading }}</th>@endforeach
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
        @forelse ($plans as $plan)
            <tr>
                <td class="px-4 py-3">{{ $plan->display_order }}</td>
                <td class="px-4 py-3">{{ $plan->badge_label ?: '—' }}</td>
                <td class="px-4 py-3 font-medium">{{ $plan->name }} @if($plan->is_highlighted)<span class="text-xs text-indigo-600">Destacado</span>@endif<br><span class="text-xs text-slate-500">{{ $plan->category_label }}</span></td>
                <td class="px-4 py-3">{{ $plan->requires_quote ? 'A cotizar' : '$'.number_format((float) $plan->monthly_price, 2) }}</td>
                <td class="px-4 py-3">@if($plan->promotional_price !== null)${{ number_format((float) $plan->promotional_price, 2) }} / {{ $plan->promotional_months }} meses @else—@endif</td>
                <td class="px-4 py-3"><x-ui.badge :status="$plan->requires_quote" /></td>
                <td class="px-4 py-3"><x-ui.badge :status="$plan->is_active" /></td>
                <td class="px-4 py-3"><div class="flex gap-2"><x-ui.button variant="secondary" :href="route('admin.plans.edit', $plan)">Editar</x-ui.button><form method="POST" action="{{ route('admin.plans.toggle-active', $plan) }}">@csrf @method('PATCH')<x-ui.button variant="secondary" type="submit">Cambiar estado</x-ui.button></form></div></td>
            </tr>
        @empty
            <tr><td colspan="8"><x-ui.empty-state>No hay planes.</x-ui.empty-state></td></tr>
        @endforelse
        </tbody>
    </x-ui.table>
    <div class="mt-5">{{ $plans->links() }}</div>
</x-app.layout>
