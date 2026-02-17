<!-- views/liaison.php -->
<?php
// Traitement des messages de la session/query params
$message = '';
$messageType = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = $_GET['message'] ?? 'Liaison effectuée avec succès';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $messageType = 'error';
}

// S'assurer que les variables existent
$donsNonLies = $donsNonLies ?? [];
$besoins = $besoins ?? [];
$villes = $villes ?? [];

// Récupérer le nonce CSP
$nonce = \Flight::get('csp_nonce') ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Dons</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" href="assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="assets/images/favicon.png" />
</head>

<body>
    <script type="application/json" id="donsData">
        <?= json_encode($donsNonLies, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <script type="application/json" id="besoinsData">
        <?= json_encode($besoins, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>

    <div class="container-scroller">
        <?php Flight::render('./inc/navbar.php'); ?>

        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper">

                <div class="content container-fluid py-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body bg-primary text-white rounded-3">
                            <h1 class="h3 mb-2"><i class="fa fa-money text-warning"></i> Lier un don à un besoin</h1>
                        </div>
                    </div>
                    <?php if ($message): ?>
                        <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                            <?= htmlspecialchars($message) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="/liaison/lier" id="liaisonForm">
                        <div class="row g-4">
                            <!-- Colonne des dons -->
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-white py-3">
                                        <h2 class="h5 mb-0">
                                            Dons non liés
                                            <span class="badge bg-primary rounded-pill"><?= count($donsNonLies) ?></span>
                                        </h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="villeFilterDon" class="form-label fw-bold">Filtrer les dons par ville :</label>
                                            <select id="villeFilterDon" class="form-select form-select-sm">
                                                <option value="">Toutes les villes</option>
                                                <?php foreach ($villes as $ville): ?>
                                                    <option value="<?= htmlspecialchars($ville['id']) ?>" data-nom="<?= htmlspecialchars($ville['nom']) ?>">
                                                        <?= htmlspecialchars($ville['nom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text" id="donFilterInfo">
                                                <span id="donVisibleCount" class="fw-bold"><?= count($donsNonLies) ?></span> dons visibles sur <span id="donTotalCount" class="fw-bold"><?= count($donsNonLies) ?></span>
                                            </div>
                                        </div>

                                        <div class="list-group" id="donsListContainer" style="max-height: 500px; overflow-y: auto;">
                                            <?php if (empty($donsNonLies)): ?>
                                                <div class="text-center py-5">
                                                    <p class="text-muted mb-3">Aucun don non lié disponible</p>
                                                    <a href="/dons/sans-besoin" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-plus"></i> Ajouter un don
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($donsNonLies as $don): ?>
                                                    <div class="list-group-item list-group-item-action position-relative don"
                                                        data-don-id="<?= htmlspecialchars($don['id']) ?>"
                                                        data-ville="<?= htmlspecialchars($don['idville_attribuee'] ?? '') ?>"
                                                        data-ville-nom="<?= htmlspecialchars($don['ville_attribuee'] ?? 'Non assignée') ?>"
                                                        id="don-<?= htmlspecialchars($don['id']) ?>">
                                                        <div class="form-check">
                                                            <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-2"
                                                                type="radio"
                                                                name="id_don"
                                                                value="<?= htmlspecialchars($don['id']) ?>"
                                                                id="radio-don-<?= htmlspecialchars($don['id']) ?>"
                                                                style="z-index: 2;">
                                                            <label class="form-check-label w-100 ps-4" for="radio-don-<?= htmlspecialchars($don['id']) ?>">
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <span class="fs-6">
                                                                        <?php if ($don['unite_type'] === 'monnaie'): ?>
                                                                            <i class="fa fa-money text-warning"></i>
                                                                        <?php else: ?>
                                                                            <i class="fa fa-inbox text-danger"></i>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                    <span class="fw-medium"><?= htmlspecialchars($don['description']) ?></span>
                                                                    <?php if (!empty($don['categorie_nom'])): ?>
                                                                        <span class="badge bg-info text-white ms-2"><?= htmlspecialchars($don['categorie_nom']) ?></span>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="small text-muted">
                                                                    <span class="fw-bold">Quantité:</span>
                                                                    <?php if ($don['unite_type'] === 'monnaie'): ?>
                                                                        <?= number_format($don['quantite_disponible'] ?? $don['quantite'], 0, ',', ' ') ?> <?= htmlspecialchars($don['unite_symbole']) ?>
                                                                        <?php if (isset($don['montant_restant']) && $don['montant_restant'] < $don['quantite']): ?>
                                                                            <span class="text-dark ms-2">(Restant: <?= number_format($don['montant_restant'], 0, ',', ' ') ?> <?= htmlspecialchars($don['unite_symbole']) ?>)</span>
                                                                        <?php endif; ?>
                                                                    <?php else: ?>
                                                                        <?= htmlspecialchars($don['quantite']) ?> <?= htmlspecialchars($don['unite_symbole']) ?>
                                                                    <?php endif; ?>
                                                                </div>
                                                                <div class="small text-muted">
                                                                    <span class="fw-bold">Ville attribuée:</span>
                                                                    <?php if (!empty($don['ville_attribuee'])): ?>
                                                                        <span class="badge bg-secondary"><?= htmlspecialchars($don['ville_attribuee']) ?></span>
                                                                    <?php else: ?>
                                                                        <span class="text-secondary">Non assignée</span>
                                                                    <?php endif; ?>
                                                                </div>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Colonne des besoins -->
                            <div class="col-md-6">
                                <div class="card shadow-sm h-100">
                                    <div class="card-header bg-white py-3">
                                        <h2 class="h5 mb-0">
                                            Besoins non satisfaits
                                            <span class="badge bg-success rounded-pill"><?= count($besoins) ?></span>
                                        </h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="villeFilter" class="form-label fw-bold">Filtrer les besoins par ville :</label>
                                            <select id="villeFilter" class="form-select form-select-sm">
                                                <option value="">Toutes les villes</option>
                                                <?php foreach ($villes as $ville): ?>
                                                    <option value="<?= htmlspecialchars($ville['id']) ?>" data-nom="<?= htmlspecialchars($ville['nom']) ?>">
                                                        <?= htmlspecialchars($ville['nom']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <div class="form-text" id="besoinFilterInfo">
                                                <span id="besoinVisibleCount" class="fw-bold"><?= count($besoins) ?></span> besoins visibles sur <span id="besoinTotalCount" class="fw-bold"><?= count($besoins) ?></span>
                                            </div>
                                        </div>

                                        <div class="list-group" id="besoinsList" style="max-height: 500px; overflow-y: auto;">
                                            <?php if (empty($besoins)): ?>
                                                <div class="text-center py-5">
                                                    <p class="text-muted mb-0">Aucun besoin non satisfait disponible</p>
                                                </div>
                                            <?php else: ?>
                                                <?php foreach ($besoins as $besoin):
                                                    $restant = $besoin['quantite_restante'];
                                                    $estPartiel = ($restant < $besoin['quantite']);
                                                ?>
                                                    <div class="list-group-item list-group-item-action position-relative besoin"
                                                        data-besoin-id="<?= htmlspecialchars($besoin['id']) ?>"
                                                        data-ville="<?= htmlspecialchars($besoin['idville']) ?>"
                                                        data-ville-nom="<?= htmlspecialchars($besoin['nom_ville']) ?>"
                                                        id="besoin-<?= htmlspecialchars($besoin['id']) ?>">
                                                        <div class="form-check">
                                                            <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-2"
                                                                type="radio"
                                                                name="id_besoin"
                                                                value="<?= htmlspecialchars($besoin['id']) ?>"
                                                                id="radio-besoin-<?= htmlspecialchars($besoin['id']) ?>"
                                                                style="z-index: 2;">
                                                            <label class="form-check-label w-100 ps-4" for="radio-besoin-<?= htmlspecialchars($besoin['id']) ?>">
                                                                <div class="d-flex align-items-center gap-2 mb-1">
                                                                    <span class="fs-6">
                                                                        <?php if ($besoin['unite_type'] === 'monnaie'): ?>
                                                                            <i class="fa fa-money text-warning"></i>
                                                                        <?php else: ?>
                                                                            <i class="fa fa-clipboard text-black"></i>
                                                                        <?php endif; ?>
                                                                    </span>
                                                                    <span class="fw-medium"><?= htmlspecialchars($besoin['description']) ?></span>
                                                                    <?php if ($estPartiel): ?>
                                                                        <span class="badge bg-warning text-dark">Partiel</span>
                                                                    <?php endif; ?>
                                                                    <?php if (!empty($besoin['categorie_nom'])): ?>
                                                                        <span class="badge bg-success"><?= htmlspecialchars($besoin['categorie_nom']) ?></span>
                                                                    <?php endif; ?>
                                                                    <span class="badge bg-secondary"><?= htmlspecialchars($besoin['nom_ville']) ?></span>
                                                                </div>
                                                                <div class="small text-muted">
                                                                    <span class="fw-bold">Total:</span> <?= htmlspecialchars($besoin['quantite']) ?> <?= htmlspecialchars($besoin['unite_symbole']) ?>
                                                                </div>
                                                                <div class="small text-muted">
                                                                    <span class="fw-bold">Déjà reçu:</span> <?= htmlspecialchars($besoin['quantite_recue'] ?? 0) ?> <?= htmlspecialchars($besoin['unite_symbole']) ?>
                                                                </div>
                                                                <div class="small">
                                                                    <span class="fw-bold">Restant:</span>
                                                                    <span class="text-success fw-bold"><?= htmlspecialchars($restant) ?> <?= htmlspecialchars($besoin['unite_symbole']) ?></span>
                                                                </div>
                                                                <?php if ($besoin['prix_unitaire']): ?>
                                                                    <div class="small text-muted">
                                                                        <span class="fw-bold">Prix unitaire:</span> <?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar
                                                                    </div>
                                                                <?php endif; ?>
                                                            </label>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Résumé de la sélection -->
                        <div class="card mt-4 shadow-sm" id="summarySection" style="display: none;">
                            <div class="card-body">
                                <h3 class="h6 fw-bold mb-3"><i class="fa fa-clipboard text-black"></i> Récapitulatif de la liaison</h3>

                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded" id="summaryDon">
                                            <h4 class="h6 fw-bold">Don sélectionné</h4>
                                            <p class="mb-1" id="donDescription">-</p>
                                            <small class="text-muted" id="donDetails"></small>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded" id="summaryBesoin">
                                            <h4 class="h6 fw-bold">Besoin sélectionné</h4>
                                            <p class="mb-1" id="besoinDescription">-</p>
                                            <small class="text-muted" id="besoinDetails"></small>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100" id="btnLier" disabled>
                                    <i class="fas fa-link"></i> Confirmer la liaison
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <?php Flight::render('./inc/footer.php'); ?>
    </div>

    <script<?= $nonce ? ' nonce="' . htmlspecialchars($nonce) . '"' : '' ?>>
        document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 DOM chargé - Initialisation...');

        let selectedDonId = null;
        let selectedBesoinId = null;

        // Récupérer les données depuis les attributs data
        const donsData = [];
        const besoinsData = [];

        // Récupérer les données des dons depuis les éléments
        document.querySelectorAll('.don').forEach(function(don) {
        const donId = don.getAttribute('data-don-id');
        const donVille = don.getAttribute('data-ville');
        const donVilleNom = don.getAttribute('data-ville-nom');

        // Extraire les informations depuis le contenu
        const description = don.querySelector('.fw-medium')?.textContent || '';
        const quantiteEl = don.querySelector('.small.text-muted:first-child');
        const quantiteText = quantiteEl?.textContent || '';

        // Déterminer si c'est de la monnaie
        const isMoney = don.querySelector('.fa-money') !== null;

        donsData.push({
        id: donId,
        description: description,
        quantite: 100, // À extraire proprement
        unite_type: isMoney ? 'monnaie' : 'unite',
        unite_symbole: isMoney ? 'Ar' : 'unité',
        ville_attribuee: donVilleNom,
        categorie_nom: don.querySelector('.badge.bg-info')?.textContent || null
        });
        });

        // Récupérer les données des besoins
        document.querySelectorAll('.besoin').forEach(function(besoin) {
        const besoinId = besoin.getAttribute('data-besoin-id');
        const besoinVille = besoin.getAttribute('data-ville');
        const besoinVilleNom = besoin.getAttribute('data-ville-nom');

        const description = besoin.querySelector('.fw-medium')?.textContent || '';
        const restantEl = besoin.querySelector('.text-success.fw-bold');
        const restant = restantEl ? parseInt(restantEl.textContent) : 0;

        besoinsData.push({
        id: besoinId,
        description: description,
        quantite_restante: restant,
        unite_symbole: 'unité',
        nom_ville: besoinVilleNom,
        categorie_nom: besoin.querySelector('.badge.bg-success')?.textContent || null
        });
        });

        console.log('📦 Dons chargés:', donsData.length);
        console.log('📋 Besoins chargés:', besoinsData.length);

        const summarySection = document.getElementById('summarySection');
        const btnLier = document.getElementById('btnLier');
        const debugSelection = document.getElementById('debugSelection');
        const donVisibleCount = document.getElementById('donVisibleCount');
        const besoinVisibleCount = document.getElementById('besoinVisibleCount');

        function updateDebug() {
        if (debugSelection) {
        debugSelection.innerHTML = 'Don sélectionné: ' + (selectedDonId || 'Aucun') + ' | Besoin sélectionné: ' + (selectedBesoinId || 'Aucun');
        }
        }

        function selectDon(id) {
        console.log('✅ Sélection don:', id);

        const card = document.getElementById('don-' + id);

        // Vérifier si le don est visible
        if (card && card.style.display === 'none') {
        alert('Ce don n\'est pas visible avec le filtre actuel');
        return;
        }

        // Désélectionner l'ancien
        if (selectedDonId) {
        const prevCard = document.getElementById('don-' + selectedDonId);
        if (prevCard) {
        prevCard.classList.remove('active', 'selected');
        prevCard.style.backgroundColor = '';
        }
        }

        // Sélectionner le nouveau
        if (card) {
        card.classList.add('active', 'selected');
        card.style.backgroundColor = '#e7f1ff';

        // Cocher le radio
        const radio = document.getElementById('radio-don-' + id);
        if (radio) radio.checked = true;

        selectedDonId = id;
        console.log('✓ Don sélectionné avec succès:', id);
        }

        updateDebug();
        updateSummary();
        }

        function selectBesoin(id) {
        console.log('✅ Sélection besoin:', id);

        const card = document.getElementById('besoin-' + id);

        if (card && card.style.display === 'none') {
        alert('Ce besoin n\'est pas visible avec le filtre actuel');
        return;
        }

        // Désélectionner l'ancien
        if (selectedBesoinId) {
        const prevCard = document.getElementById('besoin-' + selectedBesoinId);
        if (prevCard) {
        prevCard.classList.remove('active', 'selected');
        prevCard.style.backgroundColor = '';
        }
        }

        // Sélectionner le nouveau
        if (card) {
        card.classList.add('active', 'selected');
        card.style.backgroundColor = '#e7f1ff';

        const radio = document.getElementById('radio-besoin-' + id);
        if (radio) radio.checked = true;

        selectedBesoinId = id;
        console.log('✓ Besoin sélectionné avec succès:', id);
        }

        updateDebug();
        updateSummary();
        }

        function verifierCompatibiliteUnites(don, besoin) {
        // Simplifié pour l'exemple
        return { compatible: true };
        }

        function updateSummary() {
        console.log('🔄 Mise à jour du résumé...', {selectedDonId, selectedBesoinId});

        if (selectedDonId && selectedBesoinId) {
        const don = donsData.find(d => d.id == selectedDonId);
        const besoin = besoinsData.find(b => b.id == selectedBesoinId);

        if (don && besoin) {
        // Mettre à jour le résumé
        document.getElementById('donDescription').textContent = don.description || '-';
        document.getElementById('donDetails').textContent =
        (don.categorie_nom ? 'Catégorie: ' + don.categorie_nom : '') +
        (don.ville_attribuee ? ' | Ville: ' + don.ville_attribuee : '');

        document.getElementById('besoinDescription').textContent = besoin.description || '-';
        document.getElementById('besoinDetails').textContent =
        'Restant: ' + (besoin.quantite_restante || 0) + ' ' + besoin.unite_symbole +
        (besoin.categorie_nom ? ' | Catégorie: ' + besoin.categorie_nom : '') +
        ' | Ville: ' + besoin.nom_ville;

        btnLier.disabled = false;
        summarySection.style.display = 'block';

        // Supprimer les anciens avertissements
        const oldWarning = document.querySelector('.alert-warning');
        if (oldWarning) oldWarning.remove();
        }
        } else {
        summarySection.style.display = 'none';
        btnLier.disabled = true;
        }
        }

        // Attachement des événements pour les dons (CORRECTION: utilisation de .don au lieu de .item-card.don)
        const donCards = document.querySelectorAll('.don');
        console.log('📌 Attachement des événements à ' + donCards.length + ' cartes de dons');

        donCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
        // Éviter la sélection si on clique sur le radio directement
        if (e.target.type === 'radio') return;

        const donId = this.getAttribute('data-don-id');
        console.log('🖱️ Clic sur don card, ID:', donId);
        selectDon(donId);
        });

        // Sélectionner aussi quand on clique sur le label
        const radio = card.querySelector('input[type="radio"]');
        if (radio) {
        radio.addEventListener('change', function(e) {
        const donId = this.value;
        selectDon(donId);
        });
        }
        });

        // Attachement des événements pour les besoins (CORRECTION: utilisation de .besoin au lieu de .item-card.besoin)
        const besoinCards = document.querySelectorAll('.besoin');
        console.log('📌 Attachement des événements à ' + besoinCards.length + ' cartes de besoins');

        besoinCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
        if (e.target.type === 'radio') return;

        const besoinId = this.getAttribute('data-besoin-id');
        console.log('🖱️ Clic sur besoin card, ID:', besoinId);
        selectBesoin(besoinId);
        });

        const radio = card.querySelector('input[type="radio"]');
        if (radio) {
        radio.addEventListener('change', function(e) {
        const besoinId = this.value;
        selectBesoin(besoinId);
        });
        }
        });

        // Filtre des dons par ville
        const villeFilterDon = document.getElementById('villeFilterDon');
        if (villeFilterDon) {
        villeFilterDon.addEventListener('change', function() {
        const villeId = this.value;
        console.log('🔍 Filtrage des dons par ville:', villeId);

        const dons = document.querySelectorAll('.don');
        let visibleCount = 0;

        dons.forEach(function(don) {
        const donVille = don.getAttribute('data-ville');

        if (villeId === '' || donVille === villeId) {
        don.style.display = 'block';
        visibleCount++;
        } else {
        don.style.display = 'none';

        // Désélectionner si caché
        const donId = don.getAttribute('data-don-id');
        if (selectedDonId && donId == selectedDonId) {
        don.classList.remove('active', 'selected');
        don.style.backgroundColor = '';
        const radio = document.getElementById('radio-don-' + selectedDonId);
        if (radio) radio.checked = false;
        selectedDonId = null;
        }
        }
        });

        // Mettre à jour le compteur
        if (donVisibleCount) {
        donVisibleCount.textContent = visibleCount;
        }

        updateDebug();
        updateSummary();
        });
        }

        // Filtre des besoins par ville
        const villeFilter = document.getElementById('villeFilter');
        if (villeFilter) {
        villeFilter.addEventListener('change', function() {
        const villeId = this.value;
        console.log('🔍 Filtrage des besoins par ville:', villeId);

        const besoins = document.querySelectorAll('.besoin');
        let visibleCount = 0;

        besoins.forEach(function(besoin) {
        const besoinVille = besoin.getAttribute('data-ville');

        if (villeId === '' || besoinVille === villeId) {
        besoin.style.display = 'block';
        visibleCount++;
        } else {
        besoin.style.display = 'none';

        // Désélectionner si caché
        const besoinId = besoin.getAttribute('data-besoin-id');
        if (selectedBesoinId && besoinId == selectedBesoinId) {
        besoin.classList.remove('active', 'selected');
        besoin.style.backgroundColor = '';
        const radio = document.getElementById('radio-besoin-' + selectedBesoinId);
        if (radio) radio.checked = false;
        selectedBesoinId = null;
        }
        }
        });

        // Mettre à jour le compteur
        if (besoinVisibleCount) {
        besoinVisibleCount.textContent = visibleCount;
        }

        updateDebug();
        updateSummary();
        });
        }

        // Soumission du formulaire
        const form = document.getElementById('liaisonForm');
        if (form) {
        form.addEventListener('submit', function(e) {
        console.log('📤 Soumission du formulaire...');

        const donChecked = document.querySelector('input[name="id_don"]:checked');
        const besoinChecked = document.querySelector('input[name="id_besoin"]:checked');

        if (!donChecked || !besoinChecked) {
        e.preventDefault();
        alert('Veuillez sélectionner un don et un besoin');
        return;
        }

        const btn = document.getElementById('btnLier');
        if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Traitement en cours...';
        }
        });
        }

        console.log('✅ Initialisation terminée');
        });
        </script>


</body>

</html>