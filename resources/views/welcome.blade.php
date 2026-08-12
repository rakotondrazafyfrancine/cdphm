<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CDPHM - Centre de Distribution des Produits Halieutiques</title>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0d9488">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            min-height: 100%;
            width: 100%;
        }

        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background: linear-gradient(160deg, #0f172a 0%, #0d9488 55%, #0284c7 100%);
            position: relative;
            text-align: center;
            padding: 40px 20px;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Flocons décoratifs en fond, légèrement animés */
        .flocon {
            position: absolute;
            color: rgba(255,255,255,0.12);
            font-size: 80px;
            user-select: none;
            pointer-events: none;
            animation: flotter 6s ease-in-out infinite;
        }
        .f1 { top: 8%; left: 10%; font-size: 60px; animation-delay: 0s; }
        .f2 { top: 65%; left: 85%; font-size: 100px; animation-delay: 1.5s; }
        .f3 { top: 80%; left: 8%; font-size: 50px; animation-delay: 3s; }
        .f4 { top: 15%; left: 88%; font-size: 70px; animation-delay: 4.5s; }

        @keyframes flotter {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-18px) rotate(8deg); }
        }

        /* Contenu principal avec fondu d'apparition */
        .contenu {
            display: flex;
            flex-direction: column;
            align-items: center;
            animation: apparition 0.7s ease forwards;
            opacity: 0;
            position: relative;
            z-index: 1;
        }

        @keyframes apparition {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .logo-badge {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            background: rgba(255,255,255,0.12);
            border: 2px solid rgba(255,255,255,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 56px;
            margin-bottom: 24px;
            box-shadow: 0 0 40px rgba(255,255,255,0.25);
        }

        .titre {
            font-size: 44px;
            font-weight: 900;
            color: #fff;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .sous-titre {
            font-size: 16px;
            color: rgba(255,255,255,0.85);
            margin-bottom: 36px;
            max-width: 480px;
            line-height: 1.6;
        }

        /* Cartes de fonctionnalités */
        .fonctionnalites {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            max-width: 620px;
            width: 100%;
            margin-bottom: 36px;
        }

        .carte {
            background: rgba(255,255,255,0.1);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 16px;
            padding: 18px 10px;
            backdrop-filter: blur(4px);
            transition: transform 0.2s ease, background 0.2s ease;
        }

        .carte:hover {
            transform: translateY(-4px);
            background: rgba(255,255,255,0.16);
        }

        .carte-icone {
            font-size: 28px;
            margin-bottom: 8px;
        }

        .carte-label {
            font-size: 12px;
            color: rgba(255,255,255,0.9);
            font-weight: 600;
            line-height: 1.3;
        }

        .presentation {
            font-size: 15px;
            color: rgba(255,255,255,0.9);
            line-height: 1.8;
            margin-bottom: 36px;
            max-width: 480px;
            border-top: 1px solid rgba(255,255,255,0.25);
            padding-top: 20px;
        }

        .btn-connexion {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: linear-gradient(135deg, #0d9488, #0284c7);
            color: #fff;
            font-weight: 800;
            font-size: 16px;
            padding: 16px 48px;
            border-radius: 999px;
            text-decoration: none;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 8px 24px rgba(0,0,0,0.3);
            border: 2px solid rgba(255,255,255,0.4);
        }

        .btn-connexion:hover {
            transform: translateY(-3px);
            box-shadow: 0 14px 32px rgba(0,0,0,0.35);
        }

        .footer {
            margin-top: 40px;
            font-size: 12px;
            color: rgba(255,255,255,0.6);
        }

        /* Responsive : cartes en 2 colonnes sur petit écran */
        @media (max-width: 560px) {
            .fonctionnalites {
                grid-template-columns: repeat(2, 1fr);
            }
            .titre {
                font-size: 34px;
            }
        }
    </style>
</head>
<body>

    <div class="flocon f1">❄️</div>
    <div class="flocon f2">❄️</div>
    <div class="flocon f3">❄️</div>
    <div class="flocon f4">❄️</div>

    <div class="contenu">

        <div class="logo-badge">❄️</div>
        <div class="titre">CDPHM</div>
        <div class="sous-titre">Centre de Distribution des Produits Halieutiques de Mahajanga</div>

        <!-- ===== CARTES DE FONCTIONNALITÉS ===== -->
        <div class="fonctionnalites">
            <div class="carte">
                <div class="carte-icone">🌡️</div>
                <div class="carte-label">Congélation<br>Tunnels</div>
            </div>
            <div class="carte">
                <div class="carte-icone">🧊</div>
                <div class="carte-label">Chambres<br>Froides</div>
            </div>
            <div class="carte">
                <div class="carte-icone">📦</div>
                <div class="carte-label">Suivi<br>Stock</div>
            </div>
            <div class="carte">
                <div class="carte-icone">💰</div>
                <div class="carte-label">Facturation<br>Client</div>
            </div>
        </div>

        <div class="presentation">
            Système de gestion intégré pour le suivi des lots de poissons et fruits de mer, la congélation en tunnels, le stockage en chambres froides, et la facturation client — le tout en temps réel.
        </div>

        <a href="{{ route('login') }}" class="btn-connexion">
            🔐 Entrée
        </a>

        <div class="footer">
            CDPHM Mahajanga &copy; {{ date('Y') }}
        </div>

    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>

</body>
</html>
