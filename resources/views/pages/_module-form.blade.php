@php
    $isEditing = isset($module) && $module->exists;
    $action = $isEditing ? route('modules.update', $module->id) : route('modules.store');

    $val = fn(string $key, mixed $default = '') => old($key, $isEditing ? data_get($module, $key, $default) : $default);

    $initialFields = old('fields', $isEditing ? $module->fields->toArray() : []);
    $initialRelationships = old('relationships', $isEditing ? $module->relationships->toArray() : []);
@endphp

<form action="{{ $action }}" method="POST" novalidate>
    @csrf
    @if ($isEditing)
        @method('PUT')
    @endif

    {{-- ─────────────────────────────────────────────
         Card: Basic info
    ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
        <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider mb-4">
            {{ __('orkestri::labels.basic_info') }}
        </h2>

        {{-- Name --}}
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                {{ __('orkestri::labels.name') }} <span class="text-red-500">*</span>
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
                        <span
                            class="absolute top-full left-1/2 -translate-x-1/2 border-4 border-transparent border-t-gray-800"></span>
                    </span>
                </span>
            </label>

            <input type="text" id="name" name="name" value="{{ $val('name') }}" placeholder="Ex: Post"
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

    {{-- ─────────────────────────────────────────────
         Card: Fields
    ───────────────────────────────────────────── --}}
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

        <div id="fields-list" class="space-y-3"></div>

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

    {{-- ─────────────────────────────────────────────
         Card: Relationships
         Visually distinct: blue accent instead of green,
         chain-link icon, subtle left-border on each row.
    ───────────────────────────────────────────── --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
        <div class="flex items-center justify-between mb-1">
            <div class="flex items-center gap-2">
                {{-- Chain-link icon --}}
                <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101
                             m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                </svg>
                <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wider">
                    {{ __('orkestri::labels.relationships') }}
                </h2>
            </div>
            <button type="button" onclick="addRelationship()"
                class="flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                {{ __('orkestri::labels.add_relationship') }}
            </button>
        </div>

        <p class="text-xs text-gray-400 mb-4 ml-6">
            {{ __('orkestri::labels.relationships_hint') }}
        </p>

        <div id="relationships-list" class="space-y-3"></div>

        <div id="relationships-empty"
            class="text-center py-10 border-2 border-dashed border-blue-100 rounded-lg hidden">
            <svg class="w-8 h-8 text-blue-200 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101
                         m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
            </svg>
            <p class="text-sm text-gray-400">{{ __('orkestri::labels.no_relationships') }}</p>
            <button type="button" onclick="addRelationship()"
                class="mt-2 text-sm text-blue-600 font-medium hover:underline">
                {{ __('orkestri::labels.add_first_relationship') }}
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
    // ─────────────────────────────────────────────────────────────
    // Constants & server-side data
    // ─────────────────────────────────────────────────────────────
    const FIELD_TYPES = [
        'string', 'text', 'integer', 'bigInteger', 'float',
        'decimal', 'boolean', 'date', 'dateTime', 'timestamp', 'json',
    ];

    const RELATIONSHIP_TYPES = [{
            value: 'belongsTo',
            label: 'belongsTo — this module owns the FK'
        },
        {
            value: 'hasMany',
            label: 'hasMany — related module owns the FK'
        },
        {
            value: 'hasOne',
            label: 'hasOne — related module owns the FK'
        },
        {
            value: 'belongsToMany',
            label: 'belongsToMany — pivot table'
        },
    ];

    const serverErrors = @json($errors->messages());
    const existingModules = @json($modules ?? []); // [{id, name, fields: [{name, type}]}]

    let fieldIndex = 0;
    let relationshipIndex = 0;

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────
    const toSnake = str => str
        .replace(/([A-Z])/g, '_$1')
        .toLowerCase()
        .replace(/[^a-z0-9_]/g, '_')
        .replace(/^_/, '');

    function serverError(prefix, index, key) {
        const err = serverErrors[`${prefix}.${index}.${key}`];
        return err ?
            `<p class="mt-1 text-xs text-red-500">${err[0]}</p>` :
            '';
    }

    function inputClass(prefix, index, key) {
        return serverErrors[`${prefix}.${index}.${key}`] ?
            'border-red-400 bg-red-50 focus:ring-2 focus:ring-red-200' :
            'border-gray-300 focus:border-green-500 focus:ring-2 focus:ring-green-100';
    }

    function selectOptions(items, selected) {
        return items.map(({
                value,
                label
            }) =>
            `<option value="${value}" ${selected === value ? 'selected' : ''}>${label}</option>`
        ).join('');
    }

    function fieldTypeOptions(selected) {
        return FIELD_TYPES.map(t =>
            `<option value="${t}" ${selected === t ? 'selected' : ''}>${t}</option>`
        ).join('');
    }

    // ─────────────────────────────────────────────────────────────
    // Fields
    // ─────────────────────────────────────────────────────────────
    function createFieldRow(index, data = {}) {
        const {
            name = '', type = '', label = '',
                default: def = '',
                nullable = false, required = false
        } = data;

        const div = document.createElement('div');
        div.className = 'border border-gray-200 rounded-lg p-4 bg-gray-50 relative';
        div.dataset.fieldRow = index;

        div.innerHTML = `
        <button type="button" onclick="removeField(this)"
            class="absolute top-3 right-3 p-1 rounded text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors"
            title="{{ __('orkestri::labels.remove') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.name') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="fields[${index}][name]" value="${name}"
                    placeholder="Ex: price"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${inputClass('fields', index, 'name')}"/>
                ${serverError('fields', index, 'name')}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.label') }} <span class="text-red-500">*</span>
                </label>
                <input type="text" name="fields[${index}][label]" value="${label}"
                    placeholder="Ex: Price"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${inputClass('fields', index, 'label')}"/>
                ${serverError('fields', index, 'label')}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.default') }}
                </label>
                <input type="text" name="fields[${index}][default]" value="${def}"
                    placeholder="Ex: 9.99"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${inputClass('fields', index, 'default')}"/>
                ${serverError('fields', index, 'default')}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.type') }} <span class="text-red-500">*</span>
                </label>
                <select name="fields[${index}][type]"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white ${inputClass('fields', index, 'type')}">
                    <option value="">{{ __('orkestri::labels.select_type') }}</option>
                    ${fieldTypeOptions(type)}
                </select>
                ${serverError('fields', index, 'type')}
            </div>
        </div>

        <div class="flex items-center gap-6">
            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="hidden"   name="fields[${index}][nullable]" value="0"/>
                <input type="checkbox" name="fields[${index}][nullable]" value="1" ${nullable == 1 || nullable === true ? 'checked' : ''}
                    class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"/>
                <span class="text-sm text-gray-600">{{ __('orkestri::labels.nullable') }}</span>
            </label>

            <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="hidden"   name="fields[${index}][required]" value="0"/>
                <input type="checkbox" name="fields[${index}][required]" value="1" ${required == 1 || required === true ? 'checked' : ''}
                    class="w-4 h-4 rounded border-gray-300 text-green-600 focus:ring-green-500 cursor-pointer"/>
                <span class="text-sm text-gray-600">{{ __('orkestri::labels.required') }}</span>
            </label>
        </div>`;

        return div;
    }

    function addField(data = {}) {
        document.getElementById('fields-list').appendChild(createFieldRow(fieldIndex++, data));
        updateEmptyState('fields');
    }

    function removeField(btn) {
        btn.closest('[data-field-row]').remove();
        reindexRows('fields', 'field-row');
        updateEmptyState('fields');
    }

    // ─────────────────────────────────────────────────────────────
    // Relationships
    // ─────────────────────────────────────────────────────────────

    /**
     * Build the <option> list for the related-module select.
     * Excludes the current module being edited (if any).
     */
    function moduleOptions(selected) {
        if (!existingModules.length) {
            return '<option value="" disabled>{{ __('orkestri::labels.no_modules_available') }}</option>';
        }
        return existingModules.map(m =>
            `<option value="${m.id}" ${String(selected) === String(m.id) ? 'selected' : ''}>${m.name}</option>`
        ).join('');
    }

    /**
     * Build the FK field selector for a given module id.
     * The FK column is normally stored on THIS module (e.g. post_id).
     * We still show the related module's fields so the developer can confirm
     * which field on the related side acts as the owner key (usually "id").
     */
    function relatedFieldOptions(moduleId, selected) {
        const mod = existingModules.find(m => String(m.id) === String(moduleId));
        if (!mod || !mod.fields?.length) {
            return '<option value="id" selected>id (default)</option>';
        }
        const idOption =
            `<option value="id" ${selected === 'id' || !selected ? 'selected' : ''}>id (primary key)</option>`;
        const fieldOpts = mod.fields.map(f =>
            `<option value="${f.name}" ${selected === f.name ? 'selected' : ''}>${f.name} (${f.type})</option>`
        ).join('');
        return idOption + fieldOpts;
    }

    /**
     * Suggest a FK column name based on the related module name and relationship type.
     * belongsTo  → snake(relatedName)_id  (lives on THIS table)
     * hasMany/hasOne → snake(currentName)_id (lives on RELATED table, shown as hint only)
     */
    function suggestForeignKey(relType, relatedModuleId) {
        const mod = existingModules.find(m => String(m.id) === String(relatedModuleId));
        if (!mod) return '';
        if (relType === 'belongsTo') return `${toSnake(mod.name)}_id`;
        // For hasMany/hasOne the FK lives on the other table — show as hint, not editable value
        return '';
    }

    function createRelationshipRow(index, data = {}) {
        const {
            type = 'belongsTo',
                related_module = '',
                foreign_key = '',
                owner_key = 'id',
                relation_name = '',
        } = data;

        // Pre-compute suggested FK if we already have a module selected
        const suggestedFk = foreign_key || suggestForeignKey(type, related_module);

        const div = document.createElement('div');
        // Blue left-border accent to visually separate from field rows
        div.className = 'border border-blue-100 border-l-4 border-l-blue-400 rounded-lg p-4 bg-blue-50/30 relative';
        div.dataset.relationshipRow = index;

        div.innerHTML = `
        <button type="button" onclick="removeRelationship(this)"
            class="absolute top-3 right-3 p-1 rounded text-gray-300 hover:text-red-400 hover:bg-red-50 transition-colors"
            title="{{ __('orkestri::labels.remove') }}">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>

        {{-- Row 1: type + related module --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mb-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.relationship_type') }} <span class="text-red-500">*</span>
                </label>
                <select name="relationships[${index}][type]"
                    onchange="onRelationshipTypeChange(this)"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white
                           border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    ${selectOptions(RELATIONSHIP_TYPES, type)}
                </select>
                ${serverError('relationships', index, 'type')}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.related_module') }} <span class="text-red-500">*</span>
                </label>
                <select name="relationships[${index}][related_module]"
                    onchange="onRelatedModuleChange(this)"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white
                           border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    <option value="">{{ __('orkestri::labels.select_module') }}</option>
                    ${moduleOptions(related_module)}
                </select>
                ${serverError('relationships', index, 'related_module')}
            </div>
        </div>

        {{-- Row 2: FK details (shown/populated after module selected) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.relation_name') }}
                    <span class="ml-1 text-gray-400 font-normal normal-case">{{ __('orkestri::labels.relation_name_hint') }}</span>
                </label>
                <input type="text" name="relationships[${index}][relation_name]"
                    value="${relation_name}"
                    placeholder="Ex: post, comments"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white
                           border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"/>
                ${serverError('relationships', index, 'relation_name')}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.foreign_key') }}
                    <span class="ml-1 text-gray-400 font-normal normal-case">{{ __('orkestri::labels.foreign_key_hint') }}</span>
                </label>
                <input type="text" name="relationships[${index}][foreign_key]"
                    value="${suggestedFk}"
                    data-fk-input
                    placeholder="Ex: post_id"
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white
                           border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100"/>
                ${serverError('relationships', index, 'foreign_key')}
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">
                    {{ __('orkestri::labels.owner_key') }}
                    <span class="ml-1 text-gray-400 font-normal normal-case">{{ __('orkestri::labels.owner_key_hint') }}</span>
                </label>
                <select name="relationships[${index}][owner_key]"
                    data-owner-key-select
                    class="w-full px-3 py-2 text-sm border rounded-lg outline-none transition-colors bg-white
                           border-gray-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                    ${relatedFieldOptions(related_module, owner_key)}
                </select>
                ${serverError('relationships', index, 'owner_key')}
            </div>
        </div>

        {{-- Contextual hint badge --}}
        <div data-rel-hint class="mt-3 hidden">
            <span class="inline-flex items-center gap-1.5 text-xs text-blue-600 bg-blue-50 border border-blue-100 rounded-full px-3 py-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span data-rel-hint-text></span>
            </span>
        </div>`;

        // Show hint if data pre-loaded (edit mode / validation return)
        if (related_module) {
            updateRelationshipHint(div, type, related_module);
        }

        return div;
    }

    function addRelationship(data = {}) {
        document.getElementById('relationships-list').appendChild(
            createRelationshipRow(relationshipIndex++, data)
        );
        updateEmptyState('relationships');
    }

    function removeRelationship(btn) {
        btn.closest('[data-relationship-row]').remove();
        reindexRows('relationships', 'relationship-row');
        updateEmptyState('relationships');
    }

    // ─────────────────────────────────────────────────────────────
    // Relationship change handlers
    // ─────────────────────────────────────────────────────────────

    function onRelatedModuleChange(select) {
        const row = select.closest('[data-relationship-row]');
        const typeSelect = row.querySelector('[name$="[type]"]');
        const fkInput = row.querySelector('[data-fk-input]');
        const ownerSel = row.querySelector('[data-owner-key-select]');
        const relName = row.querySelector('[name$="[relation_name]"]');

        const moduleId = select.value;
        const relType = typeSelect.value;
        const mod = existingModules.find(m => String(m.id) === String(moduleId));

        // Auto-suggest FK (only when user hasn't typed a custom value)
        if (fkInput && !fkInput.dataset.userEdited) {
            fkInput.value = suggestForeignKey(relType, moduleId);
        }

        // Auto-suggest relation name from module name (only when empty)
        if (relName && !relName.value && mod) {
            relName.value = relType === 'belongsTo' ?
                toSnake(mod.name) // e.g. "post"
                :
                toSnake(mod.name) + 's'; // e.g. "comments" (naive plural)
        }

        // Populate owner-key options from the related module's fields
        if (ownerSel) {
            ownerSel.innerHTML = relatedFieldOptions(moduleId, 'id');
        }

        updateRelationshipHint(row, relType, moduleId);
    }

    function onRelationshipTypeChange(select) {
        const row = select.closest('[data-relationship-row]');
        const modSel = row.querySelector('[name$="[related_module]"]');
        const fkInput = row.querySelector('[data-fk-input]');

        if (modSel.value && fkInput && !fkInput.dataset.userEdited) {
            fkInput.value = suggestForeignKey(select.value, modSel.value);
        }

        updateRelationshipHint(row, select.value, modSel.value);
    }

    /**
     * Show a plain-english hint describing what the relationship means
     * in context of the selected modules.
     */
    function updateRelationshipHint(row, relType, moduleId) {
        const hintWrap = row.querySelector('[data-rel-hint]');
        const hintText = row.querySelector('[data-rel-hint-text]');
        if (!hintWrap || !hintText) return;

        const mod = existingModules.find(m => String(m.id) === String(moduleId));
        if (!mod) {
            hintWrap.classList.add('hidden');
            return;
        }

        const currentModuleName = '{{ $module->name ?? 'This module' }}';
        const messages = {
            belongsTo: `${currentModuleName} stores ${toSnake(mod.name)}_id and belongs to one ${mod.name}`,
            hasMany: `${currentModuleName} has many ${mod.name} records (${mod.name} stores the FK)`,
            hasOne: `${currentModuleName} has one ${mod.name} record (${mod.name} stores the FK)`,
            belongsToMany: `${currentModuleName} and ${mod.name} share a pivot table`,
        };

        hintText.textContent = messages[relType] ?? '';
        hintWrap.classList.toggle('hidden', !messages[relType]);
    }

    // Prevent FK auto-suggest from overwriting user's custom value
    document.addEventListener('input', e => {
        if (e.target.dataset.fkInput !== undefined) {
            e.target.dataset.userEdited = '1';
        }
    });

    // ─────────────────────────────────────────────────────────────
    // Shared utilities
    // ─────────────────────────────────────────────────────────────
    function updateEmptyState(section) {
        const count = document.querySelectorAll(`[data-${section.slice(0, -1)}-row]`).length;
        document.getElementById(`${section}-empty`).classList.toggle('hidden', count > 0);
    }

    function reindexRows(prefix, dataAttr) {
        document.querySelectorAll(`[data-${dataAttr}]`).forEach((row, i) => {
            row.dataset[dataAttr.replace(/-([a-z])/g, (_, c) => c.toUpperCase())] = i;
            row.querySelectorAll('[name]').forEach(el => {
                el.name = el.name.replace(new RegExp(`${prefix}\\[\\d+\\]`), `${prefix}[${i}]`);
            });
        });
        if (prefix === 'fields') fieldIndex = document.querySelectorAll('[data-field-row]').length;
        if (prefix === 'relationships') relationshipIndex = document.querySelectorAll('[data-relationship-row]').length;
    }

    // ─────────────────────────────────────────────────────────────
    // Boot: populate from initial data (edit mode / validation return)
    // ─────────────────────────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', () => {
        const initialFields = @json($initialFields);
        initialFields.length ?
            initialFields.forEach(f => addField(f)) :
            updateEmptyState('fields');

        const initialRelationships = @json($initialRelationships);
        initialRelationships.length ?
            initialRelationships.forEach(r => addRelationship(r)) :
            updateEmptyState('relationships');
    });
</script>
