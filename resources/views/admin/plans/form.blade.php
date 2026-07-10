<x-app.layout :title="$plan->exists ? 'Editar plan' : 'Nuevo plan'">
    <x-ui.page-header :title="$plan->exists ? 'Editar plan' : 'Nuevo plan'" description="Configura presentación comercial, capacidades y límites." />
    <x-ui.card><form method="POST" action="{{ $plan->exists ? route('admin.plans.update', $plan) : route('admin.plans.store') }}" class="grid gap-5 md:grid-cols-2">
        @csrf @if($plan->exists) @method('PUT') @endif
        @foreach (['name'=>'Nombre','slug'=>'Slug','category_label'=>'Categoría visual','badge_label'=>'Badge visual','secondary_label'=>'Etiqueta secundaria','billing_period'=>'Periodo'] as $name=>$label)
            <div><x-ui.label :for="$name">{{ $label }}</x-ui.label><x-ui.input :id="$name" :name="$name" :value="old($name, $plan->$name)" /><x-ui.error :name="$name" /></div>
        @endforeach
        <div class="md:col-span-2"><x-ui.label>Descripción</x-ui.label><x-ui.textarea name="description">{{ old('description', $plan->description) }}</x-ui.textarea></div>
        @foreach (['monthly_price'=>'Precio mensual normal','promotional_price'=>'Precio promocional total','promotional_months'=>'Meses de promoción','promotional_equivalent_monthly_price'=>'Equivalente mensual promocional','display_order'=>'Orden'] as $name=>$label)
            <div><x-ui.label :for="$name">{{ $label }}</x-ui.label><x-ui.input type="number" step="{{ in_array($name, ['promotional_months','display_order']) ? '1' : '0.01' }}" :id="$name" :name="$name" :value="old($name, $plan->$name ?? ($name === 'display_order' ? 0 : null))" /><x-ui.error :name="$name" /></div>
        @endforeach
        @foreach (['marketing_features'=>'Características visibles (JSON)','features'=>'Features técnicos (JSON)','limits'=>'Límites técnicos (JSON)'] as $name=>$label)
            <div class="md:col-span-2"><x-ui.label>{{ $label }}</x-ui.label><x-ui.textarea :name="$name" rows="12">{{ old($name, $plan->exists ? json_encode($plan->$name, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : ($name === 'marketing_features' ? '[]' : '{}')) }}</x-ui.textarea><x-ui.error :name="$name" /></div>
        @endforeach
        <div class="flex flex-wrap gap-6 md:col-span-2">
            <label><input type="checkbox" name="requires_quote" value="1" @checked(old('requires_quote', $plan->requires_quote))> Requiere cotización</label>
            <label><input type="checkbox" name="is_highlighted" value="1" @checked(old('is_highlighted', $plan->is_highlighted))> Plan destacado</label>
            <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $plan->exists ? $plan->is_active : true))> Plan activo</label>
        </div>
        <div class="md:col-span-2"><x-ui.button type="submit">Guardar plan</x-ui.button></div>
    </form></x-ui.card>
</x-app.layout>
