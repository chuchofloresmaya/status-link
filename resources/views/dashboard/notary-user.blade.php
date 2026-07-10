<x-app.layout title="Dashboard">
    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-indigo-600">Dashboard de usuario</p>
    <x-ui.page-header :title="'Bienvenido, '.$user->name" description="Tu espacio de trabajo en Status Link." />
    <div class="grid gap-4 sm:grid-cols-2">
        <x-ui.card><p class="text-sm text-slate-500">Notaría</p><p class="mt-2 text-xl font-bold">{{ $user->notary?->name ?? 'Sin notaría' }}</p></x-ui.card>
        <x-ui.card><p class="text-sm text-slate-500">Plan actual</p><p class="mt-2 text-xl font-bold">{{ $user->notary?->activeSubscription?->plan?->name ?? 'Sin plan' }}</p></x-ui.card>
    </div>
</x-app.layout>
