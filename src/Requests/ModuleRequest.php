<?php

namespace LucasKaiut\Orkestri\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],

            // Fields
            'fields' => ['nullable', 'array'],
            'fields.*.name' => ['required', 'string', 'max:255'],
            'fields.*.type' => ['required', 'string', 'in:' . implode(',', $this->allowedTypes())],
            'fields.*.label' => ['required', 'string', 'max:255'],
            'fields.*.nullable' => ['nullable', 'boolean'],
            'fields.*.required' => ['nullable', 'boolean'],
            'fields.*.default' => ['nullable', 'string'],

            // Relationships
            'relationships' => ['nullable', 'array'],
            'relationships.*.type' => ['required', 'string', 'in:' . implode(',', $this->allowedRelationshipTypes())],
            'relationships.*.related_module' => ['required', 'integer', 'exists:modules,id'],
            'relationships.*.foreign_key' => ['nullable', 'string', 'max:255'],
            'relationships.*.owner_key' => ['nullable', 'string', 'max:255'],
            'relationships.*.relation_name' => ['nullable', 'string', 'max:255', 'regex:/^[a-z_][a-z0-9_]*$/'],
        ];
    }

    private function allowedRelationshipTypes(): array
    {
        return ['belongsTo', 'hasMany', 'hasOne', 'belongsToMany'];
    }

    protected function prepareForValidation(): void
    {
        // Normaliza nullable e required de string/int para boolean
        $fields = collect($this->input('fields', []))
            ->map(fn($field) => array_merge($field, [
                'nullable' => filter_var($field['nullable'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'required' => filter_var($field['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
            ]))
            ->values()
            ->all();

        $this->merge(['fields' => $fields]);
    }

    private function allowedTypes(): array
    {
        return [
            'string',
            'text',
            'integer',
            'bigInteger',
            'float',
            'decimal',
            'boolean',
            'date',
            'dateTime',
            'timestamp',
            'json',
            'uuid',
        ];
    }
}
