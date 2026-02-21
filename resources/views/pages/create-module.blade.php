@extends('orkestri::layouts.app')

@section('content')
    <div class="mx-auto">
        <div class="mb-6 flex items-center gap-3">
            <a href="{{ route('modules.index') }}"
                class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 tracking-tight">{{ __('orkestri::labels.new_module') }}</h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('orkestri::labels.new_module_description') }}</p>
            </div>
        </div>

        @include('orkestri::pages._module-form')
    </div>
@endsection
