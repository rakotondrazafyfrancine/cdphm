<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>CDPHM - Connexion</title>
    {{-- Bootstrap via CDN, comme sur ton projet scolarite --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #0d1b2a, #1F497D 60%, #11294a); min-height: 100vh; display: flex; align-items: center;">
    <div class="container mt-5" style="max-width: 400px;">
        <div class="card shadow">
            <div class="card-body p-4">
                <h3 class="text-center mb-4">Connexion CDPHM</h3>

                {{-- Affiche le message d'erreur s'il y en a un --}}
                @if ($errors->any())
                    <div class="alert alert-danger">
                        {{ $errors->first() }}
                    </div>
                @endif

                {{-- Formulaire de connexion --}}
                <form method="POST" action="{{ url('/login') }}">
                    @csrf {{-- protection securite obligatoire pour les formulaires Laravel --}}

                    <div class="mb-3">
                        <label class="form-label">Pseudo</label>
                        <input type="text" name="pseudo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Mot de passe</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
