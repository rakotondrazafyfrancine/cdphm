<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1a3a5c">
    <title>@yield('title', 'CDPHM - Mahajanga')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">
    <style>
        body { background:#f4f6f8; font-family:'Segoe UI', Arial, sans-serif; }
        .sb { position:fixed; top:0; left:0; bottom:0; width:240px; background:#1a3a5c; display:flex; flex-direction:column; }
        .sb .m { display:flex; align-items:center; gap:12px; padding:10px 20px; color:rgba(255,255,255,.85); text-decoration:none; border-left:3px solid transparent; }
        .sb .m:hover { background:rgba(255,255,255,.08); color:#fff; }
        .sb .m.active { background:rgba(255,255,255,.14); color:#fff; font-weight:600; border-left-color:#fff; }
        .sb .out { border:1px solid rgba(255,255,255,.5); color:#fff; }
        .sb .out:hover, .sb .out:active, .sb .out:focus { background:#dc2626; border-color:#dc2626; color:#fff; }
        .main { margin-left:240px; }
    </style>
</head>
<body>
    @php
        $menu = [
            ['dashboard',      ['dashboard', 'activite.*'], 'layout-dashboard',     'Tableau de bord'],
            ['entrees.index',  ['entrees.*', 'lots.*'],     'truck',            'Entrées/Congélation'],
            ['tunnels.index',  ['tunnels.*'],               'snowflake',             'Tunnels'],
            ['chambres.index', ['chambres.*'],              'thermometer-snowflake', 'Chambres'],
            ['stock.index',    ['stock.*'],                 'boxes',            'Stock'],
            ['clients.index',  ['clients.*'],               'users',           'Clients'],
            ['produits.index', ['produits.*'],              'shopping-basket',           'Produits'],
            ['tarifs.index',   ['tarifs.*'],                'hand-coins',        'Tarifs'],
            ['factures.index', ['factures.*'],              'receipt',          'Factures'],
            ['rapports.index', ['rapports.*'],              'chart-column',   'Rapports'],
        ];
    @endphp

    <aside class="sb">
        <a href="{{ route('dashboard') }}" class="p-3 border-bottom border-secondary text-white text-decoration-none">
            <strong class="fs-5">CDPHM</strong>
            <div class="small opacity-75">Gestion des stocks</div>
        </a>

        <nav class="flex-grow-1 overflow-auto py-2">
            @foreach($menu as [$route, $actifs, $icone, $label])
                <a class="m {{ request()->routeIs(...$actifs) ? 'active' : '' }}" href="{{ route($route) }}">
                    <x-dynamic-component :component="'lucide-' . $icone" class="lu" style="width:1.1em;height:1.1em;" /><span>{{ $label }}</span>
                </a>
            @endforeach
        </nav>

        <div class="p-3 border-top border-secondary text-white small">
            <div class="fw-semibold">{{ auth()->user()->pseudo ?? 'Utilisateur' }}</div>
            <div class="opacity-75 mb-2">{{ auth()->user()->role ?? '' }}</div>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn btn-sm w-100 out"><x-lucide-log-out class="lu me-1" style="width:1em;height:1em;vertical-align:-.125em;" />Déconnexion</button>
            </form>
        </div>
    </aside>

    <div class="main">
        <header class="bg-white border-bottom px-4 d-flex justify-content-between align-items-center sticky-top" style="height:56px;">
            <h1 class="fs-5 fw-semibold m-0" style="color:#1a3a5c;">@yield('title', 'CDPHM - Mahajanga')</h1>
            <span class="text-secondary small" id="horloge">{{ now()->format('d/m/Y H:i') }}</span>
        </header>

        <main class="p-4">
            @foreach(['success', 'error'] as $type)
                @if(session($type))
                    <div class="alert alert-{{ $type == 'error' ? 'danger' : 'success' }} alert-dismissible fade show">
                        {{ session($type) }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
            @endforeach

            @yield('content')
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
    <script>
        function horloge() {
            document.getElementById('horloge').textContent = new Date().toLocaleString('fr-FR',
                { timeZone: 'Indian/Antananarivo', dateStyle: 'short', timeStyle: 'short' }).replace(',', '');
        }
        horloge();
        setInterval(horloge, 10000);
        if ('serviceWorker' in navigator) navigator.serviceWorker.register('/sw.js');
    </script>
</body>
</html>
