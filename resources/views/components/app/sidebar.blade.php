<aside class="border-slate-800 bg-slate-950 text-slate-300 lg:fixed lg:inset-y-0 lg:w-64">
    <div class="flex h-16 items-center border-b border-slate-800 px-5">
        <span class="mr-3 grid size-8 place-items-center rounded-lg bg-indigo-500 font-bold text-white">S</span>
        <div><p class="font-semibold text-white">Status Link</p><p class="text-xs text-slate-500">Legal operations</p></div>
    </div>
    <nav class="flex gap-1 overflow-x-auto p-3 lg:block lg:space-y-1">
        <x-app.nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">Dashboard</x-app.nav-link>
        @role('super_admin')
            <x-app.nav-link :href="route('admin.notaries.index')" :active="request()->routeIs('admin.notaries.*')">Notarías</x-app.nav-link>
            <x-app.nav-link :href="route('admin.plans.index')" :active="request()->routeIs('admin.plans.*')">Planes</x-app.nav-link>
            <x-app.nav-link :href="route('admin.subscriptions.index')" :active="request()->routeIs('admin.subscriptions.*')">Suscripciones</x-app.nav-link>
            <x-app.nav-link :href="route('admin.payments.index')" :active="request()->routeIs('admin.payments.*')">Pagos</x-app.nav-link>
            <x-app.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">Usuarios</x-app.nav-link>
        @endrole
        @role('notary_admin')
            <x-app.nav-link :href="route('app.users.index')" :active="request()->routeIs('app.users.*')">Usuarios</x-app.nav-link>
            <x-app.nav-link :href="route('app.settings.edit')" :active="request()->routeIs('app.settings.*')">Configuración</x-app.nav-link>
        @endrole
    </nav>
</aside>
