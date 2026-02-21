@extends('orkestri::layouts.app')

@section('content')
<div class="max-w-3xl mx-auto">

    {{-- Header --}}
    <div class="mb-6 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('modules.index') }}"
               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 tracking-tight">{{ $module->name }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('orkestri::labels.view_module_description') }}</p>
            </div>
        </div>
    </div>

    {{-- Card: Informações básicas --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-5">
        <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-4">
            {{ __('orkestri::labels.basic_info') }}
        </h2>

        <div class="grid grid-cols-2 gap-6">

            {{-- Name --}}
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                    {{ __('orkestri::labels.name') }}
                </p>
                <p class="text-sm font-medium text-gray-800">{{ $module->name }}</p>
            </div>

            {{-- Criado em --}}
            <div>
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                    {{ __('orkestri::labels.created_at') }}
                </p>
                <p class="text-sm text-gray-700">{{ $module->created_at->format('d/m/Y H:i') }}</p>
            </div>

            {{-- Description --}}
            <div class="col-span-2">
                <p class="text-xs font-medium text-gray-400 uppercase tracking-wide mb-1">
                    {{ __('orkestri::labels.description') }}
                </p>
                @if ($module->description)
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $module->description }}</p>
                @else
                    <p class="text-sm text-gray-400 italic">{{ __('orkestri::labels.no_description') }}</p>
                @endif
            </div>

        </div>
    </div>

    {{-- Card: Fields --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden mb-5">

        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-xs font-semibold text-gray-500 uppercase tracking-wider">
                {{ __('orkestri::labels.fields') }}
            </h2>
            <span class="text-xs font-medium text-gray-400 bg-gray-100 px-2 py-0.5 rounded-full">
                {{ $module->fields->count() }}
            </span>
        </div>

        @if ($module->fields->isEmpty())
            <div class="text-center py-10">
                <svg class="w-8 h-8 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M9 17v-2m3 2v-4m3 4v-6M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                </svg>
                <p class="text-sm text-gray-400">{{ __('orkestri::labels.no_fields') }}</p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">
                            {{ __('orkestri::labels.name') }}
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">
                            {{ __('orkestri::labels.type') }}
                        </th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">
                            {{ __('orkestri::labels.nullable') }}
                        </th>
                        <th class="text-center text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">
                            {{ __('orkestri::labels.required') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($module->fields as $field)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-6 py-3 font-medium text-gray-800">
                                {{ $field->name }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-100">
                                    {{ $field->type }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if ($field->nullable)
                                    <svg class="w-4 h-4 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                            <td class="px-6 py-3 text-center">
                                @if ($field->required)
                                    <svg class="w-4 h-4 text-green-500 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-gray-300 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Footer --}}
    <div class="flex items-center justify-between text-xs text-gray-400">
        <span>{{ __('orkestri::labels.updated_at') }}: {{ $module->updated_at->format('d/m/Y H:i') }}</span>
        <form action="{{ route('modules.destroy', $module->id) }}" method="POST"
              onsubmit="return confirm('{{ __('orkestri::labels.confirm_delete') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="flex items-center gap-1.5 text-red-400 hover:text-red-600 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                {{ __('orkestri::labels.delete') }}
            </button>
        </form>
    </div>

</div>
@endsection