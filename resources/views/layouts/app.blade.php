<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0d9488">
    <title>@yield('title', 'CDPHM - Mahajanga')</title>

    <!-- ==========================================
    BOOTSTRAP 5 + ICONS + GOOGLE FONTS
    ========================================== -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="manifest" href="/manifest.json">

    <style>
        /* ==========================================
        VARIABLES COULEURS
        ========================================== */
        :root {
            --primary: #1a3a5c;
            --primary-light: #2a5a8c;
            --secondary: #e8edf3;
            --accent: #f39c12;
            --success: #27ae60;
            --danger: #e74c3c;
            --dark: #0d1b2a;
            --text-muted: #8a9bb5;
        }

        /* ==========================================
        STYLES GLOBAUX
        ========================================== */
        * {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        }

        body {
            background: #f0f2f6;
            min-height: 100vh;
        }

        /* ==========================================
        HEADER PRINCIPAL
        ========================================== */
        .app-header {
            background: linear-gradient(135deg, var(--dark), var(--primary));
            padding: 0 28px;
            height: 76px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 4px solid var(--accent);
            position: sticky;
            top: 0;
            z-index: 1050;
            box-shadow: 0 4px 24px rgba(0,0,0,0.30);
        }

        .app-header .brand {
            display: flex;
            align-items: center;
            gap: 16px;
            color: #fff;
            text-decoration: none;
        }

        .app-header .brand .logo-icon {
            width: 50px;
            height: 50px;
            background: rgba(255,255,255,0.10);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255,255,255,0.08);
        }

        .app-header .brand .brand-text {
            line-height: 1.2;
        }

        .app-header .brand .brand-text .title {
            font-weight: 800;
            font-size: 1.4rem;
            letter-spacing: 0.5px;
            color: #fff;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .app-header .brand .brand-text .title .badge-version {
            font-size: 0.55rem;
            font-weight: 600;
            background: rgba(243, 156, 18, 0.25);
            color: var(--accent);
            padding: 2px 10px;
            border-radius: 30px;
            border: 1px solid rgba(243, 156, 18, 0.2);
        }

        .app-header .brand .brand-text .subtitle {
            font-size: 0.7rem;
            font-weight: 400;
            color: rgba(255,255,255,0.6);
            letter-spacing: 0.3px;
            display: block;
            line-height: 1.3;
        }

        .app-header .brand .brand-text .subtitle .location {
            font-size: 0.65rem;
            color: rgba(255,255,255,0.4);
            display: block;
            margin-top: 1px;
        }

        .app-header .brand .brand-text .subtitle i {
            margin: 0 4px;
        }

        /* ==========================================
        PARTIE DROITE DU HEADER
        ========================================== */
        .app-header .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .app-header .header-right .date-badge {
            background: rgba(255,255,255,0.07);
            padding: 6px 18px;
            border-radius: 30px;
            color: rgba(255,255,255,0.8);
            font-size: 0.8rem;
            border: 1px solid rgba(255,255,255,0.06);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .app-header .header-right .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .app-header .header-right .user-info .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--accent), #e67e22);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 1rem;
            border: 2px solid rgba(255,255,255,0.2);
            flex-shrink: 0;
        }

        .app-header .header-right .user-info .user-name {
            color: #fff;
            font-weight: 500;
            font-size: 0.9rem;
        }

        .app-header .header-right .user-info .user-name small {
            display: block;
            font-weight: 400;
            font-size: 0.7rem;
            color: rgba(255,255,255,0.5);
        }

        .btn-logout {
            background: rgba(231, 76, 60, 0.15);
            border: 1px solid rgba(231, 76, 60, 0.25);
            color: #f1948a;
            border-radius: 30px;
            padding: 6px 20px;
            font-size: 0.8rem;
            font-weight: 500;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .btn-logout:hover {
            background: var(--danger);
            border-color: var(--danger);
            color: #fff;
        }

        /* ==========================================
        MENU DE NAVIGATION (PROJET CDPHM)
        ========================================== */
        .app-nav {
            background: #fff;
            padding: 0 28px;
            border-bottom: 1px solid #e2e8f0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            position: sticky;
            top: 76px;
            z-index: 1040;
        }

        .app-nav .nav-scroll {
            display: flex;
            gap: 4px;
            overflow-x: auto;
            padding: 8px 0;
            scrollbar-width: thin;
            -webkit-overflow-scrolling: touch;
        }

        .app-nav .nav-scroll::-webkit-scrollbar {
            height: 3px;
        }
        .app-nav .nav-scroll::-webkit-scrollbar-thumb {
            background: var(--primary-light);
            border-radius: 10px;
        }

        .app-nav .nav-link-custom {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-weight: 500;
            font-size: 0.85rem;
            color: #4a5a72;
            transition: all 0.2s;
            white-space: nowrap;
            text-decoration: none;
            border: none;
            background: transparent;
        }

        .app-nav .nav-link-custom .nav-emoji {
            font-size: 1.2rem;
            opacity: 0.8;
        }

        .app-nav .nav-link-custom:hover {
            background: #f0f4f9;
            color: var(--primary);
        }

        .app-nav .nav-link-custom:hover .nav-emoji {
            opacity: 1;
        }

        .app-nav .nav-link-custom.active {
            background: var(--primary);
            color: #fff;
            box-shadow: 0 4px 12px rgba(26, 58, 92, 0.30);
        }

        .app-nav .nav-link-custom.active .nav-emoji {
            opacity: 1;
        }

        /* ==========================================
        CONTENU PRINCIPAL
        ========================================== */
        .app-content {
            padding: 28px 28px 40px;
            max-width: 1440px;
            margin: 0 auto;
        }

        /* ==========================================
        RESPONSIVE
        ========================================== */
        @media (max-width: 992px) {
            .app-header .brand .brand-text .subtitle {
                display: none;
            }
            .app-header .header-right .date-badge {
                display: none;
            }
            .app-header .header-right .user-info .user-name small {
                display: none;
            }
            .app-nav .nav-link-custom {
                padding: 8px 14px;
                font-size: 0.78rem;
            }
            .app-nav .nav-link-custom .nav-text {
                display: none;
            }
        }

        @media (max-width: 576px) {
            .app-header {
                padding: 0 12px;
                height: 64px;
            }
            .app-header .brand .logo-icon {
                width: 38px;
                height: 38px;
                font-size: 20px;
            }
            .app-header .brand .brand-text .title {
                font-size: 1.1rem;
            }
            .app-header .brand .brand-text .title .badge-version {
                display: none;
            }
            .app-header .header-right .user-info .user-name {
                font-size: 0.75rem;
            }
            .btn-logout span {
                display: none;
            }
            .app-nav {
                padding: 0 12px;
            }
            .app-content {
                padding: 16px 12px 30px;
            }
        }
    </style>
</head>
<body>

    <!-- ==========================================
    HEADER
    ========================================== -->
    <header class="app-header">

        <!-- ===== BRAND / LOGO ===== -->
        <a href="{{ route('dashboard') }}" class="brand">
            <div class="logo-icon">🌊</div>
            <div class="brand-text">
                <div class="title">
                    CDPHM
                    <span class="badge-version">v2.0</span>
                </div>
                <div class="subtitle">
                    <i class="bi bi-building"></i> Centre de Distribution des Produits Halieutiques de Mahajanga
                    <span class="location">
                        <i class="bi bi-geo-alt"></i> Mahajanga - Ville des Fleurs
                    </span>
                </div>
            </div>
        </a>

        <!-- ===== PARTIE DROITE ===== -->
        <div class="header-right">

            <!-- Date et heure -->
            <div class="date-badge">
                <i class="bi bi-calendar3"></i>
                {{ now()->format('d/m/Y H:i') }}
            </div>

            <!-- Informations utilisateur -->
            <div class="user-info">
                <div class="avatar">
                    {{ auth()->check() ? strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) : '?' }}
                </div>
                <div class="user-name">
                    {{ auth()->user()->name ?? 'Utilisateur' }}
                    <small>{{ auth()->user()->email ?? '' }}</small>
                </div>
            </div>

            <!-- Bouton déconnexion -->
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <span>Déconnexion</span>
                </button>
            </form>

        </div>
    </header>

    <!-- ==========================================
    MENU DE NAVIGATION (PROJET CDPHM)
    ========================================== -->
    <nav class="app-nav">
        <div class="nav-scroll">

            <!-- 1. Dashboard -->
            <a class="nav-link-custom {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                <span class="nav-emoji">📊</span>
                <span class="nav-text">Dashboard</span>
            </a>

            <!-- 2. Entrées -->
            <a class="nav-link-custom {{ request()->routeIs('entrees.*') || request()->routeIs('lots.*') ? 'active' : '' }}" href="{{ route('entrees.index') }}">
                <span class="nav-emoji">🚛</span>
                <span class="nav-text">Entrées/Congelation</span>
            </a>

            <!-- 3. Tunnels -->
            <a class="nav-link-custom {{ request()->routeIs('tunnels.*') ? 'active' : '' }}" href="{{ route('tunnels.index') }}">
                <span class="nav-emoji">❄️</span>
                <span class="nav-text">Tunnels</span>
            </a>

            <!-- 4. Chambres -->
            <a class="nav-link-custom {{ request()->routeIs('chambres.*') ? 'active' : '' }}" href="{{ route('chambres.index') }}">
                <span class="nav-emoji">🧊</span>
                <span class="nav-text">Chambres</span>
            </a>

            <!-- 5. Stock -->
            <a class="nav-link-custom {{ request()->routeIs('stock.*') ? 'active' : '' }}" href="{{ route('stock.index') }}">
                <span class="nav-emoji">📦</span>
                <span class="nav-text">Stock</span>
            </a>

            <!-- 7. Clients -->
            <a class="nav-link-custom {{ request()->routeIs('clients.*') ? 'active' : '' }}" href="{{ route('clients.index') }}">
                <span class="nav-emoji">👤</span>
                <span class="nav-text">Clients</span>
            </a>

            <!-- 8. Produits -->
            <a class="nav-link-custom {{ request()->routeIs('produits.*') ? 'active' : '' }}" href="{{ route('produits.index') }}">
                <span class="nav-emoji">🐟</span>
                <span class="nav-text">Produits</span>
            </a>

            <!-- 9. Tarifs -->
            <a class="nav-link-custom {{ request()->routeIs('tarifs.*') ? 'active' : '' }}" href="{{ route('tarifs.index') }}">
                <span class="nav-emoji">💰</span>
                <span class="nav-text">Tarifs</span>
            </a>

            <!-- 10. Rapports -->
            <a class="nav-link-custom {{ request()->routeIs('rapports.*') ? 'active' : '' }}" href="{{ route('rapports.index') }}">
                <span class="nav-emoji">📈</span>
                <span class="nav-text">Rapports</span>
            </a>

        </div>
    </nav>

    <!-- ==========================================
    CONTENU PRINCIPAL
    ========================================== -->
    <main class="app-content">

        <!-- Messages flash -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4" role="alert">
                <i class="bi bi-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <!-- Contenu de la page -->
        @yield('content')

    </main>

    <!-- ==========================================
    SCRIPTS
    ========================================== -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')


<script>
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('/sw.js')
           .then(() => console.log('Service Worker enregistre'))
           .catch((err) => console.log('Erreur SW:', err));
    }
    </script>
</body>
</html>
