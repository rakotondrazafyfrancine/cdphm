<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Impression - Tunnel {{ $tunnel->nom }}</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
        .info { margin-bottom: 10px; }
        .info strong { display: inline-block; width: 150px; }
        .footer { margin-top: 30px; text-align: center; border-top: 1px solid #ccc; padding-top: 10px; font-size: 12px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>CDPHM - Tunnel de congélation</h1>
        <p>Date d'impression : {{ now()->format('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <strong>ID :</strong> {{ $tunnel->id }}
    </div>
    <div class="info">
        <strong>Nom :</strong> {{ $tunnel->nom }}
    </div>
    <div class="info">
        <strong>Capacité :</strong> {{ number_format($tunnel->capacite, 0) }} kg
    </div>
    <div class="info">
        <strong>Statut :</strong> {{ $tunnel->statut }}
    </div>

    <div class="footer">
        CDPHM — {{ now()->format('Y') }} · Tous droits réservés
    </div>

    <script>
        window.print();
    </script>
</body>
</html>
