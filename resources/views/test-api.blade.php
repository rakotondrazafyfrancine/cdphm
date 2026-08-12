<!DOCTYPE html>
<html>
<head>
    <title>Test API</title>
</head>
<body>
    <h1>Test API</h1>

    <form method="POST" action="/api/lots">
        @csrf
        <input type="text" name="client_id" placeholder="Client ID" value="1">
        <input type="text" name="produit_id" placeholder="Produit ID" value="1">
        <input type="text" name="poids_entree" placeholder="Poids" value="100">
        <input type="text" name="date_entree" placeholder="Date" value="2026-07-31">
        <button type="submit">Envoyer</button>
    </form>
</body>
</html>
