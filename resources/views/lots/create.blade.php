@extends('layouts.app')

@section('title', 'Nouvelle arrivée')

@section('content')
@if($errors->any())
    <div class="alert alert-danger">
        <ul class="mb-0">
            @foreach($errors->all() as $erreur)
                <li>{{ $erreur }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="card border-0" style="border-radius:6px;box-shadow:0 1px 3px rgba(0,0,0,.08);">
    <div class="card-header bg-white fw-semibold" style="color:#1a3a5c;">Fiche de contrôle de l'arrivée</div>
    <div class="card-body">
        <form action="{{ route('lots.store') }}" method="POST" id="form-entree">
            @csrf

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Collecteur / Client <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select name="client_id" id="client-select" class="form-select" style="border-radius:4px 0 0 4px;" required>
                            <option value="">-- Sélectionner --</option>
                            @foreach($clients as $client)
                                <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->nom }}</option>
                            @endforeach
                        </select>
                        <button type="button" id="btn-plus" class="btn btn-outline-secondary" style="border-radius:0 4px 4px 0;font-weight:700;" title="Ajouter un nouveau client">+</button>
                    </div>
                    <div id="zone-client" class="border rounded p-2 mt-2" style="display:none;background:#f8fafc;">
                        <input type="text" id="nouveau-nom" class="form-control form-control-sm mb-2" style="border-radius:4px;" placeholder="Nom complet / Coopérative">
                        <input type="text" id="nouveau-contact" class="form-control form-control-sm mb-2" style="border-radius:4px;" placeholder="Numéro (Ex : +261 34 00 000 00)">
                        <button type="button" id="btn-client" class="btn btn-sm text-white w-100" style="background:#1a3a5c;border-radius:4px;">Enregistrer le client</button>
                        <small id="msg-client" class="d-block mt-1"></small>
                    </div>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Espèce / Produit <span class="text-danger">*</span></label>
                    <input type="hidden" name="espece" id="espece" value="{{ old('espece') }}">
                    <select id="produit-select" class="form-select" style="border-radius:4px;"></select>
                    <input type="text" id="autre-produit" class="form-control mt-2" style="display:none;border-radius:4px;" placeholder="Nom du nouveau produit">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Poids brut d'entrée (kg) <span class="text-danger">*</span></label>
                    <input type="number" name="poids_entree" id="poids-input" class="form-control" style="border-radius:4px;" step="0.01" min="0.1" value="{{ old('poids_entree') }}" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Tunnel de congélation</label>
                    <select name="tunnel_id" class="form-select" style="border-radius:4px;">
                        <option value="">-- Sélectionner --</option>
                        @foreach($tunnels as $tunnel)
                            <option value="{{ $tunnel->id }}" {{ old('tunnel_id', $tunnelSelectionne ?? '') == $tunnel->id ? 'selected' : '' }}>{{ $tunnel->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Chambre froide (optionnel)</label>
                    <select name="chambre_id" class="form-select" style="border-radius:4px;">
                        <option value="">-- Choisir --</option>
                        @foreach($chambres as $chambre)
                            <option value="{{ $chambre->id }}" {{ old('chambre_id', $chambreSelectionne ?? '') == $chambre->id ? 'selected' : '' }}>{{ $chambre->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Durée (heures)</label>
                    <input type="number" name="heures_tunnel" id="heures-input" class="form-control" style="border-radius:4px;" value="{{ old('heures_tunnel', 24) }}" min="1">
                </div>

                <div class="col-md-4">
                    <label class="form-label small fw-semibold">Durée (fraction de jour)</label>
                    <input type="text" id="jours-input" class="form-control bg-light" style="border-radius:4px;" value="1" readonly>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-2 text-center h-100">
                        <small class="text-muted d-block">Tarif tunnel</small>
                        <strong style="color:#1a3a5c;">{{ number_format($tarifTunnel, 0, ',', ' ') }} Ar/kg/jour</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="border rounded p-2 text-center h-100">
                        <small class="text-muted d-block">Facturation estimée</small>
                        <strong style="color:#1a3a5c;" id="estimation">0 Ariary</strong>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-center gap-3 mt-4">
                <a href="{{ route('lots.index') }}" class="btn btn-outline-secondary px-4" style="border-radius:4px;">Annuler</a>
                <button type="submit" class="btn text-white px-4" style="background:#1a3a5c;border-radius:4px;">Valider l'entrée et générer le bon</button>
            </div>
        </form>
    </div>
</div>

<script>
    const TARIF_TUNNEL  = {{ $tarifTunnel }};
    const TARIF_CHAMBRE = {{ $tarifChambre }};
    const PRODUITS      = @json($produitsParClient);

    const poids   = document.getElementById('poids-input');
    const heures  = document.getElementById('heures-input');
    const jours   = document.getElementById('jours-input');
    const estim   = document.getElementById('estimation');
    const tunnel  = document.querySelector('select[name="tunnel_id"]');
    const chambre = document.querySelector('select[name="chambre_id"]');

    // Estimation de la facturation
    function calculer() {
        const p = parseFloat(poids.value) || 0;
        const j = Math.max((parseFloat(heures.value) || 24) / 24, 1 / 24);
        let montant;
        if (chambre.value && !tunnel.value) {
            montant = p * TARIF_CHAMBRE;
            jours.value = '—';
        } else {
            montant = p * j * TARIF_TUNNEL;
            jours.value = j.toFixed(2);
        }
        estim.textContent = Math.round(montant).toLocaleString('fr-FR') + ' Ariary';
    }
    [poids, heures].forEach(el => el.addEventListener('input', calculer));
    [tunnel, chambre].forEach(el => el.addEventListener('change', calculer));
    calculer();

    // Liste des produits du client choisi
    const clientSel  = document.getElementById('client-select');
    const produitSel = document.getElementById('produit-select');
    const autre      = document.getElementById('autre-produit');
    const espece     = document.getElementById('espece');

    function majProduits(valeurInitiale) {
        const liste = PRODUITS[clientSel.value] || [];
        produitSel.innerHTML = '<option value="">-- Sélectionner --</option>'
            + liste.map(n => '<option value="' + n.replace(/"/g, '&quot;') + '">' + n + '</option>').join('')
            + '<option value="__autre__">Autre (nouveau produit)</option>';
        autre.style.display = 'none';
        autre.value = '';
        espece.value = '';

        if (valeurInitiale) {
            if (liste.includes(valeurInitiale)) {
                produitSel.value = valeurInitiale;
                espece.value = valeurInitiale;
            } else {
                produitSel.value = '__autre__';
                autre.style.display = 'block';
                autre.value = valeurInitiale;
                espece.value = valeurInitiale;
            }
        }
    }
    produitSel.addEventListener('change', function () {
        const nouveau = this.value === '__autre__';
        autre.style.display = nouveau ? 'block' : 'none';
        espece.value = nouveau ? autre.value.trim() : this.value;
        if (nouveau) autre.focus();
    });
    autre.addEventListener('input', () => espece.value = autre.value.trim());
    clientSel.addEventListener('change', () => majProduits(''));
    majProduits(espece.value);

    document.getElementById('form-entree').addEventListener('submit', function (e) {
        if (!espece.value.trim()) {
            e.preventDefault();
            alert('Choisissez un produit, ou saisissez un nouveau produit avec « Autre ».');
        }
    });

    // Bouton "+" : zone d'ajout d'un nouveau client
    document.getElementById('btn-plus').addEventListener('click', function () {
        const zone = document.getElementById('zone-client');
        zone.style.display = zone.style.display === 'none' ? 'block' : 'none';
    });

    document.getElementById('btn-client').addEventListener('click', async function () {
        const nom     = document.getElementById('nouveau-nom');
        const contact = document.getElementById('nouveau-contact');
        const msg     = document.getElementById('msg-client');
        if (!nom.value.trim()) { msg.className = 'text-danger'; msg.textContent = 'Saisissez le nom du client.'; return; }

        this.disabled = true;
        try {
            const donnees = new FormData();
            donnees.append('nom', nom.value.trim());
            donnees.append('contact', contact.value.trim());
            donnees.append('_token', '{{ csrf_token() }}');
            await fetch('{{ route('clients.store') }}', { method: 'POST', body: donnees });

            const html  = await (await fetch(window.location.href)).text();
            const liste = new DOMParser().parseFromString(html, 'text/html').querySelector('select[name="client_id"]');
            clientSel.innerHTML = liste.innerHTML;

            const option = [...clientSel.options].find(o => o.text.trim().toLowerCase() === nom.value.trim().toLowerCase());
            if (option) {
                clientSel.value = option.value;
                clientSel.dispatchEvent(new Event('change'));
                nom.value = '';
                contact.value = '';
                msg.className = 'text-success';
                msg.textContent = 'Client enregistré et sélectionné.';
            } else {
                msg.className = 'text-danger';
                msg.textContent = "Le client n'a pas pu être enregistré.";
            }
        } catch (e) {
            msg.className = 'text-danger';
            msg.textContent = "Erreur pendant l'enregistrement.";
        }
        this.disabled = false;
    });
</script>
@endsection
