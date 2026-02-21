@extends('orkestri::layouts.app')

@section('content')
    <div class="mx-auto">

        {{-- Cabeçalho --}}
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800 tracking-tight">
                    {{ __('orkestri::pages.modules_title') }}
                </h1>
                <p class="text-sm text-gray-500 mt-0.5">{{ __('orkestri::pages.modules_description') }}</p>
            </div>
            <a href="{{ route('modules.create') }}"
                class="bg-green-600 hover:bg-green-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                {{ __('orkestri::labels.new') }}
            </a>
        </div>

        {{-- Tabela --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3 w-1/4">
                            #
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3 w-1/4">
                            {{ __('orkestri::labels.name') }}
                        </th>
                        <th class="text-left text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3">
                            {{ __('orkestri::labels.migration_status') }}
                        </th>
                        <th class="text-right text-xs font-semibold text-gray-500 uppercase tracking-wider px-6 py-3 w-16">
                            {{ __('orkestri::labels.actions') }}
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($modules as $module)
                        <tr class="row-item group">
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $module->id }}</td>
                            <td class="px-6 py-4 font-medium text-gray-800">{{ $module->name }}</td>

                            {{-- Migration Status --}}
                            <td class="px-6 py-4">
                                @if ($module->migration_status === 'migrated')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-green-50 text-green-700 border border-green-100">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span>
                                        {{ __('orkestri::labels.migrated') }}
                                    </span>
                                @else
                                    <form action="{{ route('modules.run-migration', $module->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-white bg-amber-500 hover:bg-amber-600 rounded-lg transition-colors shadow-sm">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            {{ __('orkestri::labels.run_migration') }}
                                        </button>
                                    </form>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4 text-right relative">
                                <button onclick="toggleMenu('{{ $module->id }}')"
                                    class="p-1.5 rounded-md hover:bg-gray-100 text-gray-400 hover:text-gray-700 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
                                    </svg>
                                </button>
                                <div id="{{ $module->id }}"
                                    class="dropdown absolute right-6 top-10 z-10 bg-white rounded-lg shadow-lg border border-gray-100 py-1 w-36">
                                    <a href="{{ route('modules.edit', $module->id) }}"
                                        class="flex items-center gap-2 w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition-colors">
                                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        {{ __('orkestri::labels.edit') }}
                                    </a>
                                    <form action="{{ route('modules.destroy', $module->id) }}" method="POST"
                                        onsubmit="return confirm('{{ __('orkestri::labels.confirm_delete') }}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="flex items-center gap-2 w-full px-4 py-2 text-sm text-red-500 hover:bg-red-50 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            {{ __('orkestri::labels.delete') }}
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Paginação --}}
            <div class="border-t border-gray-100 px-6 py-4 flex items-center justify-between text-sm text-gray-500">
                <div>
                    {!! __('orkestri::labels.showing', [
                        'from' => '<span class="font-medium text-gray-700">' . $modules->firstItem() . '</span>',
                        'to' => '<span class="font-medium text-gray-700">' . $modules->lastItem() . '</span>',
                        'total' => '<span class="font-medium text-gray-700">' . $modules->total() . '</span>',
                    ]) !!}
                </div>

                <nav class="flex items-center gap-1" aria-label="Paginação">

                    @if ($modules->onFirstPage())
                        <span
                            class="flex items-center gap-1 px-3 py-1.5 rounded-md text-gray-300 cursor-not-allowed select-none">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            {{ __('orkestri::labels.previous') }}
                        </span>
                    @else
                        <a href="{{ $modules->previousPageUrl() }}"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-md hover:bg-gray-100 transition-colors text-gray-700">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            {{ __('orkestri::labels.previous') }}
                        </a>
                    @endif

                    @foreach ($modules->getUrlRange(1, $modules->lastPage()) as $page => $url)
                        @if ($page === $modules->currentPage() - 2 && $modules->currentPage() > 4)
                            <span class="px-1 text-gray-300">...</span>
                        @endif

                        @if ($page === 1 || $page === $modules->lastPage() || abs($page - $modules->currentPage()) <= 1)
                            @if ($page === $modules->currentPage())
                                <span class="px-3 py-1.5 rounded-md bg-green-600 text-white font-medium">
                                    {{ $page }}
                                </span>
                            @else
                                <a href="{{ $url }}"
                                    class="px-3 py-1.5 rounded-md hover:bg-gray-100 transition-colors text-gray-700">
                                    {{ $page }}
                                </a>
                            @endif
                        @endif

                        @if ($page === $modules->currentPage() + 2 && $page < $modules->lastPage())
                            <span class="px-1 text-gray-300">...</span>
                        @endif
                    @endforeach

                    @if ($modules->hasMorePages())
                        <a href="{{ $modules->nextPageUrl() }}"
                            class="flex items-center gap-1 px-3 py-1.5 rounded-md hover:bg-gray-100 transition-colors text-gray-700">
                            {{ __('orkestri::labels.next') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    @else
                        <span
                            class="flex items-center gap-1 px-3 py-1.5 rounded-md text-gray-300 cursor-not-allowed select-none">
                            {{ __('orkestri::labels.next') }}
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    @endif

                </nav>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu(id) {
            document.querySelectorAll('.dropdown').forEach(el => {
                if (el.id !== id) el.classList.remove('open');
            });
            document.getElementById(id).classList.toggle('open');
        }

        document.addEventListener('click', e => {
            if (!e.target.closest('button[onclick]')) {
                document.querySelectorAll('.dropdown').forEach(el => el.classList.remove('open'));
            }
        });
    </script>
@endsection
