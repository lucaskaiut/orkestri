@php
    $isEditing = isset($module) && $module->exists;
    $action = $isEditing ? route('modules.update', $module->id) : route('modules.store');

    $val = fn(string $key, mixed $default = '') => old($key, $isEditing ? data_get($module, $key, $default) : $default);

    $initialFields = old('fields', $isEditing ? $module->fields->toArray() : []);
@endphp

<form action="{{ $action }}" method="POST" novalidate>
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    {{-- Card: Informações básicas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
            {{ __('orkestri::labels.basic_info') }}
        </h2>

        {{-- Name --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('orkestri::labels.name') }} <span class="text-red-500">*</span>

                {{-- Tooltip --}}
                <span class="relative inline-block align-middle ml-1 group">
                    <svg class="w-3.5 h-3.5 text-gray-400 cursor-help" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <span
                        class="pointer-events-none absolute bottom-full left-1/2 -translate-x-1/2 mb-2 w-56
                         bg-gray-800 text-white text-xs rounded-lg px-3 py-2 shadow-lg
                         opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-50">
                        {{ __('orkestri::labels.module_name_tooltip') }}
                        {{-- Seta --}}
                        <span
                            class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                    </span>
                </span>
            </label>

            <input type="text" id="name" name="name" value="{{ $val('name') }}" placeholder="Ex: Product"
                class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors
               {{ $errors->has('name')
                   ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200'
                   : 'border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100' }}" />

            @error('name')
                <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>

        {{-- Description --}}
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('orkestri::labels.description') }}
            </label>
            <textarea id="description" name="description" rows="3" placeholder="Ex: Manages the product catalog"
                class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors resize-none
                       {{ $errors->has('description')
                           ? 'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200'
                           : 'border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100' }}">{{ $val('description') }}</textarea>
            @error('description')
                <p class="mt-1 text-xs text-red-500 flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ $message }}
                </p>
            @enderror
        </div>
    </div>

    {{-- Card: Fields --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">

        <div class="flex items-center justify-between mb-4">
            <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                {{ __('orkestri::labels.fields') }}
            </h2>
            <button type="button" onclick="addField()"
                class="flex items-center gap-1.5 text-sm font-medium text-green-600 hover:text-green-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('orkestri::labels.add_field') }}
            </button>
        </div>

        {{-- Lista de fields --}}
        <div id="fields-list" class="space-y-3"></div>

        {{-- Empty state --}}
        <div id="fields-empty" class="text-center py-10 border-2 border-dashed border-gray-200 rounded-lg hidden">
            <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z" />
            </svg>
            <p class="text-sm text-gray-400">{{ __('orkestri::labels.no_fields') }}</p>
            <button type="button" onclick="addField()" class="mt-2 text-sm text-green-600 font-medium hover:underline">
                {{ __('orkestri::labels.add_first_field') }}
            </button>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-end gap-3">
        <a href="{{ route('modules.index') }}"
            class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800 hover:bg-gray-100 rounded-lg transition-colors">
            {{ __('orkestri::labels.cancel') }}
        </a>
        <button type="submit"
            class="px-5 py-2 text-sm font-medium bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors shadow-sm">
            {{ $isEditing ? __('orkestri::labels.update') : __('orkestri::labels.save') }}
        </button>
    </div>

</form>

<script>
    const TYPES = ['string', 'text', 'integer', 'bigInteger', 'float', 'decimal', 'boolean', 'date', 'dateTime',
        'timestamp', 'json',
    ];
    const serverErrors = @json($errors->messages());
    let fieldIndex = 0;

    function typeOptions(selected) {
        return TYPES.map(t =>
            `<option value="${t}" ${selected === t ? 'selected' : ''}>${t}</option>`
        ).join('');
    }

    function fieldError(index, key) {
        const err = serverErrors[`fields.${index}.${key}`];
        if (!err) return '';
        return `<p class="mt-1 text-xs text-red-500">${err[0]}</p>`;
    }

    function fieldInputClass(index, key) {
        return serverErrors[`fields.${index}.${key}`] ?
            'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' :
            'border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100';
    }

    function createFieldRow(index, data = {}) {
        const name = data.name ?? '';
        const type = data.type ?? '';
        const label = data.label ?? '';
        const defaultValue = data.default ?? '';
        const nullable = data.nullable == 1 || data.nullable === true;
        const required = data.required == 1 || data.required === true;

        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50 relative';
        div.dataset.fieldRow = index;

        div.innerHTML = `
            <button type="button" onclick="removeField(this)"
                    class="absolute top-3 right-3 p-1 rounded text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ __('orkestri::labels.name') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="fields[${index}][name]"
                        value="${name}"
                        placeholder="Ex: price"
                        class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${fieldInputClass(index, 'name')}"
                    />
                    ${fieldError(index, 'name')}
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ __('orkestri::labels.label') }} <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        name="fields[${index}][label]"
                        value="${label}"
                        placeholder="Ex: Price"
                        class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${fieldInputClass(index, 'label')}"
                    />
                    ${fieldError(index, 'label')}
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ __('orkestri::labels.default') }}
                    </label>
                    <input
                        type="text"
                        name="fields[${index}][default]"
                        value="${defaultValue}"
                        placeholder="Ex: 9.99"
                        class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${fieldInputClass(index, 'default')}"
                    />
                    ${fieldError(index, 'default')}
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">
                        {{ __('orkestri::labels.type') }} <span class="text-red-500">*</span>
                    </label>
                    <select
                        name="fields[${index}][type]"
                        class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${fieldInputClass(index, 'type')}"
                    >
                        <option value="">{{ __('orkestri::labels.select_type') }}</option>
                        ${typeOptions(type)}
                    </select>
                    ${fieldError(index, 'type')}
                </div>
            </div>

            <div class="flex items-center gap-6">
                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="hidden" name="fields[${index}][nullable]" value="0"/>
                    <input type="checkbox" name="fields[${index}][nullable]" value="1" ${nullable ? 'checked' : ''}
                           class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"/>
                    <span class="text-sm text-gray-600">{{ __('orkestri::labels.nullable') }}</span>
                </label>

                <label class="flex items-center gap-2 cursor-pointer select-none">
                    <input type="hidden" name="fields[${index}][required]" value="0"/>
                    <input type="checkbox" name="fields[${index}][required]" value="1" ${required ? 'checked' : ''}
                           class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"/>
                    <span class="text-sm text-gray-600">{{ __('orkestri::labels.required') }}</span>
                </label>
            </div>
        `;

        return div;
    }

    function addField(data = {}) {
        const list = document.getElementById('fields-list');
        list.appendChild(createFieldRow(fieldIndex++, data));
        updateEmptyState();
    }

    function removeField(btn) {
        btn.closest('[data-field-row]').remove();
        reindexFields();
        updateEmptyState();
    }

    function reindexFields() {
        document.querySelectorAll('[data-field-row]').forEach((row, i) => {
            row.dataset.fieldRow = i;
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(/fields\[\d+\]/, `fields[${i}]`);
            });
        });
        fieldIndex = document.querySelectorAll('[data-field-row]').length;
    }

    function updateEmptyState() {
        const empty = document.getElementById('fields-empty');
        const count = document.querySelectorAll('[data-field-row]').length;
        empty.classList.toggle('hidden', count > 0);
    }

    // Popula os fields iniciais (modo edição ou retorno de validação)
    document.addEventListener('DOMContentLoaded', () => {
        const initial = @json($initialFields);
        if (initial.length > 0) {
            initial.forEach(f => addField(f));
        } else {
            updateEmptyState();
        }
    });
</script>
