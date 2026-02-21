<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Orkestri UI</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet" />
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }

        .dropdown {
            display: none;
        }

        .dropdown.open {
            display: block;
        }

        tr.row-item td {
            transition: background 0.18s, color 0.18s;
        }

        tr.row-item:hover td {
            background-color: #f0fdf4;
        }

        tr.row-item:hover td:first-child {
            border-left: 3px solid #16a34a;
            padding-left: calc(1.5rem - 3px);
        }
    </style>
</head>

<body class="bg-gray-100">

    <div class="min-h-screen flex">

        {{-- Sidebar --}}
        <aside class="w-64 bg-white shadow-sm flex flex-col">

            {{-- Logo --}}
            <div class="px-6 py-5 border-b border-gray-100">
                <span class="text-lg font-semibold text-gray-800 tracking-tight">Orkestri</span>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-3 py-4 space-y-0.5">

                @php
                    $navItem = function (string $routeName, string $label, string $icon) {
                        $active = request()->routeIs($routeName . '*');
                        $base = 'flex items-center gap-3 px-3 py-2 rounded-lg text-sm font-medium transition-colors';
                        $style = $active
                            ? "$base bg-green-50 text-green-700"
                            : "$base text-gray-600 hover:bg-gray-100 hover:text-gray-900";
                        $iconColor = $active ? 'text-green-600' : 'text-gray-400 group-hover:text-gray-500';
                        return [$active, $style, $iconColor];
                    };
                @endphp

                {{-- Módulos --}}
                @php [$active, $style, $iconColor] = $navItem('modules', 'Módulos', '') @endphp
                <a href="{{ route('modules.index') }}" class="{{ $style }} group">
                    <svg class="w-5 h-5 {{ $iconColor }} shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                    {{ __('orkestri::labels.modules') }}

                    @if ($active)
                        <span class="ml-auto w-1.5 h-1.5 rounded-full bg-green-500"></span>
                    @endif
                </a>

            </nav>

            {{-- Footer --}}
            <div class="px-4 py-4 border-t border-gray-100 text-xs text-gray-400">
                v1.0.0
            </div>

        </aside>

        {{-- Content --}}
        <main class="flex-1 p-6">
            @yield('content')
        </main>

    </div>

</body>

</html>
