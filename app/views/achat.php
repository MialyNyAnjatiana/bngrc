<?php
// Traitement des messages de la session/query params
$message = '';
$messageType = '';

if (isset($_GET['success']) && $_GET['success'] == 1) {
    $message = $_GET['message'] ?? 'Achat effectué avec succès';
    $messageType = 'success';
} elseif (isset($_GET['error'])) {
    $message = $_GET['error'];
    $messageType = 'error';
}

// S'assurer que les variables existent
$donsArgent = $donsArgent ?? [];
$besoins = $besoins ?? [];
$villes = $villes ?? [];
$achats = $achats ?? [];
$statsVilles = $statsVilles ?? [];

// Récupérer le nonce CSP
$nonce = \Flight::get('csp_nonce') ?? '';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Achats avec dons en argent</title>
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
        <?= json_encode($donsArgent, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>
    <script type="application/json" id="besoinsData">
        <?= json_encode($besoins, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>
    </script>

    <div class="container-scroller">
        <?php Flight::render('./inc/navbar.php'); ?>
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper">
                <div class="container-fluid py-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body bg-primary text-white rounded-3">
                            <h1 class="h3 mb-2"><i class="fa fa-money text-warning"></i> Achats avec Dons en Argent</h1>
                            <p class="mb-0 opacity-75">Achetez des besoins en nature/matériaux avec les dons en argent</p>
                        </div>
                    </div>

                    <div class="content">
                        <?php if ($message): ?>
                            <div class="alert <?= $messageType === 'success' ? 'alert-success' : 'alert-danger' ?> alert-dismissible fade show" role="alert">
                                <?= htmlspecialchars($message) ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- Tabs -->
                        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="achat-tab" data-bs-toggle="tab" data-bs-target="#tab-achat" type="button" role="tab">Nouvel Achat</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="historique-tab" data-bs-toggle="tab" data-bs-target="#tab-historique" type="button" role="tab">Historique des Achats</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="stats-tab" data-bs-toggle="tab" data-bs-target="#tab-stats" type="button" role="tab">Statistiques</button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content" id="myTabContent">
                            <!-- Onglet Nouvel Achat -->
                            <div class="tab-pane fade show active" id="tab-achat" role="tabpanel">
                                <form method="POST" action="/achat/effectuer" id="achatForm">
                                    <div class="row g-4">
                                        <!-- Dons en argent -->
                                        <div class="col-md-6">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header bg-white py-3">
                                                    <h2 class="h5 mb-0">
                                                        Dons en argent disponibles
                                                        <span class="badge bg-primary rounded-pill"><?= count($donsArgent) ?></span>
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
                                                            <span id="donVisibleCount" class="fw-bold"><?= count($donsArgent) ?></span> dons visibles sur <span id="donTotalCount" class="fw-bold"><?= count($donsArgent) ?></span>
                                                        </div>
                                                    </div>

                                                    <div class="list-group" id="donsList" style="max-height: 400px; overflow-y: auto;">
                                                        <?php if (empty($donsArgent)): ?>
                                                            <div class="text-center py-5">
                                                                <p class="text-muted mb-0">Aucun don en argent disponible</p>
                                                            </div>
                                                        <?php else: ?>
                                                            <?php foreach ($donsArgent as $don):
                                                                $montantDispo = $don['montant_restant'] ?? $don['quantite'];
                                                                $villeAttribuee = $don['ville_attribuee'] ?? '';
                                                                $villeId = $don['idville_attribuee'] ?? '';
                                                            ?>
                                                                <div class="list-group-item list-group-item-action position-relative don"
                                                                    data-don-id="<?= htmlspecialchars($don['id']) ?>"
                                                                    data-don-montant="<?= htmlspecialchars($montantDispo) ?>"
                                                                    data-ville="<?= htmlspecialchars($villeId) ?>"
                                                                    data-ville-nom="<?= htmlspecialchars($villeAttribuee) ?>"
                                                                    id="don-<?= htmlspecialchars($don['id']) ?>">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-2"
                                                                            type="radio"
                                                                            name="iddon"
                                                                            value="<?= htmlspecialchars($don['id']) ?>"
                                                                            id="radio-don-<?= htmlspecialchars($don['id']) ?>"
                                                                            style="z-index: 2;">
                                                                        <label class="form-check-label w-100 ps-4" for="radio-don-<?= htmlspecialchars($don['id']) ?>">
                                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                                <span class="fs-6"><i class="fa fa-money text-warning"></i></span>
                                                                                <span class="fw-medium"><?= htmlspecialchars($don['description']) ?></span>
                                                                                <?php if (!empty($villeAttribuee) && $villeAttribuee != 'Non assignée'): ?>
                                                                                    <span class="badge bg-secondary"><?= htmlspecialchars($villeAttribuee) ?></span>
                                                                                <?php endif; ?>
                                                                            </div>
                                                                            <div class="small text-muted">
                                                                                <span class="fw-bold">Montant total:</span> <?= number_format($don['quantite'], 0, ',', ' ') ?> Ar
                                                                            </div>
                                                                            <div class="small">
                                                                                <span class="fw-bold">Disponible:</span>
                                                                                <span class="text-success fw-bold"><?= number_format($montantDispo, 0, ',', ' ') ?> Ar</span>
                                                                            </div>
                                                                            <?php if (!empty($villeAttribuee) && $villeAttribuee != 'Non assignée'): ?>
                                                                                <div class="small text-muted">
                                                                                    <span class="fw-bold">Ville:</span> <?= htmlspecialchars($villeAttribuee) ?>
                                                                                </div>
                                                                            <?php else: ?>
                                                                                <div class="small text-muted">
                                                                                    <span class="fw-bold">Ville:</span> <span class="text-secondary">Non assignée</span>
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

                                        <!-- Besoins achetables -->
                                        <div class="col-md-6">
                                            <div class="card shadow-sm h-100">
                                                <div class="card-header bg-white py-3">
                                                    <h2 class="h5 mb-0">
                                                        Besoins achetables
                                                        <span class="badge bg-success rounded-pill"><?= count($besoins) ?></span>
                                                    </h2>
                                                </div>
                                                <div class="card-body">
                                                    <div class="mb-3">
                                                        <label for="villeFilterBesoin" class="form-label fw-bold">Filtrer les besoins par ville :</label>
                                                        <select id="villeFilterBesoin" class="form-select form-select-sm">
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

                                                    <div class="list-group" id="besoinsList" style="max-height: 300px; overflow-y: auto;">
                                                        <?php if (empty($besoins)): ?>
                                                            <div class="text-center py-5">
                                                                <p class="text-muted mb-0">Aucun besoin achetable disponible</p>
                                                            </div>
                                                        <?php else: ?>
                                                            <?php foreach ($besoins as $besoin):
                                                                $restant = $besoin['quantite_restante'];
                                                            ?>
                                                                <div class="list-group-item list-group-item-action position-relative besoin"
                                                                    data-besoin-id="<?= htmlspecialchars($besoin['id']) ?>"
                                                                    data-ville="<?= htmlspecialchars($besoin['idville']) ?>"
                                                                    data-prix="<?= htmlspecialchars($besoin['prix_unitaire']) ?>"
                                                                    data-restant="<?= htmlspecialchars($restant) ?>"
                                                                    id="besoin-<?= htmlspecialchars($besoin['id']) ?>">
                                                                    <div class="form-check">
                                                                        <input class="form-check-input position-absolute top-50 start-0 translate-middle-y ms-2"
                                                                            type="radio"
                                                                            name="idbesoin"
                                                                            value="<?= htmlspecialchars($besoin['id']) ?>"
                                                                            id="radio-besoin-<?= htmlspecialchars($besoin['id']) ?>"
                                                                            style="z-index: 2;">
                                                                        <label class="form-check-label w-100 ps-4" for="radio-besoin-<?= htmlspecialchars($besoin['id']) ?>">
                                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                                <span class="fs-6"><i class="fa fa-clipboard text-black"></i></span>
                                                                                <span class="fw-medium"><?= htmlspecialchars($besoin['description']) ?></span>
                                                                                <?php if ($restant < $besoin['quantite']): ?>
                                                                                    <span class="badge bg-warning text-dark">Partiel</span>
                                                                                <?php endif; ?>
                                                                                <span class="badge bg-secondary"><?= htmlspecialchars($besoin['ville_nom']) ?></span>
                                                                            </div>
                                                                            <div class="small text-muted">
                                                                                <span class="fw-bold">Ville:</span> <?= htmlspecialchars($besoin['ville_nom']) ?>
                                                                            </div>
                                                                            <div class="small">
                                                                                <span class="fw-bold">Prix unitaire:</span>
                                                                                <span class="text-primary fw-bold"><?= number_format($besoin['prix_unitaire'], 0, ',', ' ') ?> Ar</span>
                                                                            </div>
                                                                            <div class="small text-muted">
                                                                                <span class="fw-bold">Restant:</span> <?= htmlspecialchars($restant) ?> <?= htmlspecialchars($besoin['unite_symbole'] ?? '') ?>
                                                                            </div>
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            <?php endforeach; ?>
                                                        <?php endif; ?>
                                                    </div>

                                                    <!-- Quantité à acheter -->
                                                    <div class="card mt-3 bg-light">
                                                        <div class="card-body">
                                                            <div class="row align-items-end">
                                                                <div class="col-md-6">
                                                                    <label for="quantite" class="form-label fw-bold">Quantité à acheter :</label>
                                                                    <input type="number" class="form-control" id="quantite" name="quantite" min="1" value="1">
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="p-2 bg-white rounded border">
                                                                        <small class="text-muted d-block">Montant total:</small>
                                                                        <span class="h5 mb-0 text-primary" id="montantTotal">0</span> <small>Ar</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Résumé de la sélection -->
                                    <div class="card mt-4 shadow-sm" id="summarySection" style="display: none;">
                                        <div class="card-body">
                                            <h3 class="h6 fw-bold mb-3"><i class="fa fa-clipboard text-black"></i> Récapitulatif de l'achat</h3>

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

                                            <button type="submit" class="btn btn-success w-100" id="btnAcheter" disabled>
                                                <i class="fas fa-shopping-cart"></i> Effectuer l'achat
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- Onglet Historique -->
                            <div class="tab-pane fade" id="tab-historique" role="tabpanel">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white py-3">
                                        <h2 class="h5 mb-0"><i class="fa fa-history"></i> Historique des achats</h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <select id="villeFilterHistorique" class="form-select form-select-sm">
                                                    <option value="">Toutes les villes</option>
                                                    <?php foreach ($villes as $ville): ?>
                                                        <option value="<?= htmlspecialchars($ville['id']) ?>">
                                                            <?= htmlspecialchars($ville['nom']) ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped" id="historiqueTable">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Ville</th>
                                                        <th>Don</th>
                                                        <th>Besoin</th>
                                                        <th>Quantité</th>
                                                        <th>Montant</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($achats)): ?>
                                                        <tr>
                                                            <td colspan="6" class="text-center text-muted py-4">Aucun achat effectué</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($achats as $achat): ?>
                                                            <tr data-ville="<?= htmlspecialchars($achat['ville_id']) ?>">
                                                                <td><?= date('d/m/Y H:i', strtotime($achat['date_achat'])) ?></td>
                                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($achat['ville_nom']) ?></span></td>
                                                                <td><?= htmlspecialchars($achat['don_description']) ?></td>
                                                                <td><?= htmlspecialchars($achat['besoin_description']) ?></td>
                                                                <td><?= htmlspecialchars($achat['quantite']) ?> <?= htmlspecialchars($achat['besoin_unite']) ?></td>
                                                                <td class="fw-bold"><?= number_format($achat['montant'], 0, ',', ' ') ?> Ar</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Onglet Statistiques -->
                            <div class="tab-pane fade" id="tab-stats" role="tabpanel">
                                <div class="card shadow-sm">
                                    <div class="card-header bg-white py-3">
                                        <h2 class="h5 mb-0"><i class="fa fa-bar-chart-o"></i> Statistiques par ville</h2>
                                    </div>
                                    <div class="card-body">
                                        <div class="row g-4 mb-4">
                                            <div class="col-md-6">
                                                <div class="card bg-primary text-white">
                                                    <div class="card-body">
                                                        <h3 class="h6 mb-2">Total des achats</h3>
                                                        <div class="h3 mb-0">
                                                            <?php
                                                            $total = array_sum(array_column($statsVilles, 'total_montant'));
                                                            echo number_format($total, 0, ',', ' ') . ' Ar';
                                                            ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="card bg-success text-white">
                                                    <div class="card-body">
                                                        <h3 class="h6 mb-2">Nombre d'achats</h3>
                                                        <div class="h3 mb-0">
                                                            <?= array_sum(array_column($statsVilles, 'nombre_achats')) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-hover table-striped">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Ville</th>
                                                        <th>Nombre d'achats</th>
                                                        <th>Quantité totale</th>
                                                        <th>Montant total</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php if (empty($statsVilles)): ?>
                                                        <tr>
                                                            <td colspan="4" class="text-center text-muted py-4">Aucune statistique</td>
                                                        </tr>
                                                    <?php else: ?>
                                                        <?php foreach ($statsVilles as $stat): ?>
                                                            <tr>
                                                                <td><span class="badge bg-secondary"><?= htmlspecialchars($stat['ville_nom']) ?></span></td>
                                                                <td><?= htmlspecialchars($stat['nombre_achats']) ?></td>
                                                                <td><?= htmlspecialchars($stat['total_quantite']) ?></td>
                                                                <td class="fw-bold"><?= number_format($stat['total_montant'], 0, ',', ' ') ?> Ar</td>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    <?php endif; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php Flight::render('./inc/footer.php'); ?>
    </div>

    <script<?= $nonce ? ' nonce="' . htmlspecialchars($nonce) . '"' : '' ?>>
        document.addEventListener('DOMContentLoaded', function() {
        console.log('🚀 Initialisation de la page achats...');

        // Variables globales
        let selectedDonId = null;
        let selectedBesoinId = null;
        let selectedDonMontant = 0;
        let selectedBesoinPrix = 0;
        let selectedBesoinRestant = 0;

        // Récupérer les données depuis les attributs data
        const donsData = [];
        const besoinsData = [];

        // Extraire les données des dons
        document.querySelectorAll('.don').forEach(function(don) {
        const donId = don.getAttribute('data-don-id');
        const donMontant = don.getAttribute('data-don-montant');
        const description = don.querySelector('.fw-medium')?.textContent || '';

        donsData.push({
        id: donId,
        description: description,
        montant_restant: parseFloat(donMontant) || 0,
        quantite: parseFloat(donMontant) || 0
        });
        });

        // Extraire les données des besoins
        document.querySelectorAll('.besoin').forEach(function(besoin) {
        const besoinId = besoin.getAttribute('data-besoin-id');
        const prix = besoin.getAttribute('data-prix');
        const restant = besoin.getAttribute('data-restant');
        const description = besoin.querySelector('.fw-medium')?.textContent || '';
        const villeNom = besoin.querySelector('.badge.bg-secondary')?.textContent || '';
        const uniteEl = besoin.querySelector('.small.text-muted:last-child');
        const unite = uniteEl ? (uniteEl.textContent.match(/[A-Za-z]+$/) || [''])[0] : '';

        besoinsData.push({
        id: besoinId,
        description: description,
        ville_nom: villeNom,
        prix_unitaire: parseFloat(prix) || 0,
        quantite_restante: parseInt(restant) || 0,
        unite: unite
        });
        });

        console.log('Dons chargés:', donsData.length);
        console.log('Besoins chargés:', besoinsData.length);

        // Éléments DOM
        const summarySection = document.getElementById('summarySection');
        const btnAcheter = document.getElementById('btnAcheter');
        const quantiteInput = document.getElementById('quantite');

        // Bootstrap tabs sont gérés automatiquement, pas besoin de code manuel

        // Sélection don
        const donCards = document.querySelectorAll('.don');
        donCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
        // Ignorer si on clique sur le radio
        if (e.target.type === 'radio') return;

        const id = this.getAttribute('data-don-id');

        // Retirer sélection précédente
        if (selectedDonId) {
        const oldCard = document.getElementById('don-' + selectedDonId);
        if (oldCard) {
        oldCard.classList.remove('active', 'selected');
        oldCard.style.backgroundColor = '';
        }
        }

        // Nouvelle sélection
        this.classList.add('active', 'selected');
        this.style.backgroundColor = '#e7f1ff';

        const radio = document.getElementById('radio-don-' + id);
        if (radio) radio.checked = true;

        selectedDonId = id;
        selectedDonMontant = parseFloat(this.getAttribute('data-don-montant'));

        updateSummary();
        updateButton();
        });

        // Gérer le clic sur le radio directement
        const radio = card.querySelector('input[type="radio"]');
        if (radio) {
        radio.addEventListener('change', function() {
        const id = this.value;
        const card = document.getElementById('don-' + id);

        // Retirer sélection précédente
        if (selectedDonId && selectedDonId != id) {
        const oldCard = document.getElementById('don-' + selectedDonId);
        if (oldCard) {
        oldCard.classList.remove('active', 'selected');
        oldCard.style.backgroundColor = '';
        }
        }

        card.classList.add('active', 'selected');
        card.style.backgroundColor = '#e7f1ff';

        selectedDonId = id;
        selectedDonMontant = parseFloat(card.getAttribute('data-don-montant'));

        updateSummary();
        updateButton();
        });
        }
        });

        // Sélection besoin
        const besoinCards = document.querySelectorAll('.besoin');
        besoinCards.forEach(function(card) {
        card.addEventListener('click', function(e) {
        if (e.target.type === 'radio') return;

        const id = this.getAttribute('data-besoin-id');

        // Vérifier si visible
        if (this.style.display === 'none') {
        alert('Ce besoin n\'est pas visible avec le filtre actuel');
        return;
        }

        // Retirer sélection précédente
        if (selectedBesoinId) {
        const oldCard = document.getElementById('besoin-' + selectedBesoinId);
        if (oldCard) {
        oldCard.classList.remove('active', 'selected');
        oldCard.style.backgroundColor = '';
        }
        }

        // Nouvelle sélection
        this.classList.add('active', 'selected');
        this.style.backgroundColor = '#e7f1ff';

        const radio = document.getElementById('radio-besoin-' + id);
        if (radio) radio.checked = true;

        selectedBesoinId = id;
        selectedBesoinPrix = parseFloat(this.getAttribute('data-prix'));
        selectedBesoinRestant = parseInt(this.getAttribute('data-restant'));

        // Mettre à jour max quantité
        quantiteInput.max = selectedBesoinRestant;
        quantiteInput.value = 1;

        updateSummary();
        calculerMontant();
        updateButton();
        });

        // Gérer le clic sur le radio directement
        const radio = card.querySelector('input[type="radio"]');
        if (radio) {
        radio.addEventListener('change', function() {
        const id = this.value;
        const card = document.getElementById('besoin-' + id);

        if (card.style.display === 'none') {
        alert('Ce besoin n\'est pas visible avec le filtre actuel');
        this.checked = false;
        return;
        }

        // Retirer sélection précédente
        if (selectedBesoinId && selectedBesoinId != id) {
        const oldCard = document.getElementById('besoin-' + selectedBesoinId);
        if (oldCard) {
        oldCard.classList.remove('active', 'selected');
        oldCard.style.backgroundColor = '';
        }
        }

        card.classList.add('active', 'selected');
        card.style.backgroundColor = '#e7f1ff';

        selectedBesoinId = id;
        selectedBesoinPrix = parseFloat(card.getAttribute('data-prix'));
        selectedBesoinRestant = parseInt(card.getAttribute('data-restant'));

        quantiteInput.max = selectedBesoinRestant;
        quantiteInput.value = 1;

        updateSummary();
        calculerMontant();
        updateButton();
        });
        }
        });

        // Calcul du montant
        function calculerMontant() {
        const quantite = parseInt(quantiteInput.value) || 0;
        const montant = quantite * selectedBesoinPrix;
        const montantSpan = document.getElementById('montantTotal');
        montantSpan.textContent = montant.toLocaleString('fr-FR');

        // Vérifier si montant disponible
        if (selectedDonMontant > 0 && montant > selectedDonMontant) {
        montantSpan.style.color = 'red';
        } else {
        montantSpan.style.color = 'inherit';
        }

        updateButton();
        }

        // Mise à jour bouton
        function updateButton() {
        const quantite = parseInt(quantiteInput.value) || 0;
        const montant = quantite * selectedBesoinPrix;

        if (selectedDonId && selectedBesoinId && quantite > 0 &&
        quantite <= selectedBesoinRestant && montant <=selectedDonMontant) {
            btnAcheter.disabled=false;
            } else {
            btnAcheter.disabled=true;
            }
            }

            // Mise à jour résumé
            function updateSummary() {
            if (selectedDonId && selectedBesoinId) {
            const don=donsData.find(function(d) { return d.id==selectedDonId; });
            const besoin=besoinsData.find(function(b) { return b.id==selectedBesoinId; });

            if (don && besoin) {
            document.getElementById('donDescription').textContent=don.description || '-' ;
            document.getElementById('donDetails').textContent='Disponible: ' + (don.montant_restant || 0).toLocaleString('fr-FR') + ' Ar' ;

            document.getElementById('besoinDescription').textContent=besoin.description + ' (' + besoin.ville_nom + ')' ;
            document.getElementById('besoinDetails').textContent='Prix: ' + besoin.prix_unitaire.toLocaleString('fr-FR') + ' Ar/' + besoin.unite + ' - Restant: ' + besoin.quantite_restante + ' ' + besoin.unite;

            summarySection.style.display='block' ;
            }
            } else {
            summarySection.style.display='none' ;
            }
            }

            // Événement quantité
            if (quantiteInput) {
            quantiteInput.addEventListener('input', function() {
            let val=parseInt(this.value) || 0;
            if (val < 1) this.value=1;
            if (selectedBesoinRestant> 0 && val > selectedBesoinRestant) {
            this.value = selectedBesoinRestant;
            }
            calculerMontant();
            updateButton();
            });
            }

            // Filtre besoins par ville
            const villeFilterBesoin = document.getElementById('villeFilterBesoin');
            if (villeFilterBesoin) {
            villeFilterBesoin.addEventListener('change', function() {
            const villeId = this.value;
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
            selectedBesoinPrix = 0;
            selectedBesoinRestant = 0;
            updateSummary();
            updateButton();
            }
            }
            });

            // Mettre à jour badge
            const badge = document.querySelector('.col-md-6:last-child .badge.bg-success');
            if (badge) badge.textContent = visibleCount;

            // Mettre à jour le compteur
            const besoinVisibleCount = document.getElementById('besoinVisibleCount');
            if (besoinVisibleCount) besoinVisibleCount.textContent = visibleCount;
            });
            }

            // Filtre dons par ville
            const villeFilterDon = document.getElementById('villeFilterDon');
            if (villeFilterDon) {
            villeFilterDon.addEventListener('change', function() {
            const villeId = this.value;
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
            selectedDonMontant = 0;
            updateSummary();
            updateButton();
            }
            }
            });

            // Mettre à jour badge
            const badge = document.querySelector('.col-md-6:first-child .badge.bg-primary');
            if (badge) badge.textContent = visibleCount;

            // Mettre à jour le compteur
            const donVisibleCount = document.getElementById('donVisibleCount');
            if (donVisibleCount) donVisibleCount.textContent = visibleCount;
            });
            }

            // Filtre historique par ville
            const villeFilterHistorique = document.getElementById('villeFilterHistorique');
            if (villeFilterHistorique) {
            villeFilterHistorique.addEventListener('change', function() {
            const villeId = this.value;
            const rows = document.querySelectorAll('#historiqueTable tbody tr');

            rows.forEach(function(row) {
            const rowVille = row.getAttribute('data-ville');
            if (villeId === '' || rowVille === villeId) {
            row.style.display = '';
            } else {
            row.style.display = 'none';
            }
            });
            });
            }

            // Soumission formulaire
            const achatForm = document.getElementById('achatForm');
            if (achatForm) {
            achatForm.addEventListener('submit', function(e) {
            const donChecked = document.querySelector('input[name="iddon"]:checked');
            const besoinChecked = document.querySelector('input[name="idbesoin"]:checked');
            const quantite = parseInt(quantiteInput.value) || 0;

            if (!donChecked || !besoinChecked || quantite <= 0) {
                e.preventDefault();
                alert('Veuillez sélectionner un don, un besoin et une quantité valide');
                } else {
                btnAcheter.disabled=true;
                btnAcheter.innerHTML='<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Traitement en cours...' ;
                }
                });
                }

                console.log('✅ Initialisation terminée');
                });
                </script>


</body>

</html>