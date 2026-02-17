<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Vente</title>
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
    <div class="container-scroller">
        <?php Flight::render('./inc/navbar.php'); ?>
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper p-5">
                <div class="container py-4">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body bg-primary text-white rounded-3">
                            <h1 class="h3 mb-2"><i class="fa fa-money text-warning"></i> Vente de Dons</h1>
                        </div>
                    </div>

                    <!-- Messages de notification -->
                    <?php if (isset($_GET['success']) && $_GET['success'] == 1): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <strong><i class="fa fa-check-circle me-1"></i> Succès!</strong> <?= htmlspecialchars($_GET['message'] ?? 'Opération réussie') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <strong><i class="fa fa-exclamation-circle me-1"></i> Erreur!</strong> <?= htmlspecialchars($_GET['error']) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Affichage des besoins correspondants en cas d'erreur de vente -->
                    <?php if (isset($_GET['error']) && isset($_GET['besoins']) && isset($_GET['don_id'])): ?>
                        <?php
                        $besoinsData = json_decode(urldecode($_GET['besoins']), true);
                        $donId = $_GET['don_id'];
                        ?>

                        <div class="card border-danger mb-4" id="besoinsSection">
                            <div class="card-header bg-danger text-white">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="fs-1"><i class="fa fa-exclamation-triangle"></i></span>
                                    <div>
                                        <h2 class="h5 mb-1">Vente impossible</h2>
                                        <p class="mb-0 small opacity-75"><?= htmlspecialchars($_GET['error']) ?></p>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <h3 class="h6 text-danger mb-3"><i class="fa fa-clipboard-list me-2"></i> Besoins non satisfaits dans cette ville :</h3>

                                <?php if (!empty($besoinsData)): ?>
                                    <div class="vstack gap-3">
                                        <?php foreach ($besoinsData as $besoin):
                                            $quantiteTotale = (float)($besoin['quantite'] ?? 1);
                                            $quantiteRecue = (float)($besoin['quantite_recue'] ?? 0);
                                            $pourcentageRempli = ($quantiteRecue / $quantiteTotale) * 100;
                                        ?>
                                            <div class="card border-0 bg-light">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start flex-wrap">
                                                        <div class="flex-grow-1">
                                                            <h4 class="h6 fw-bold mb-3">
                                                                <?= htmlspecialchars($besoin['description'] ?? 'Sans description') ?>
                                                            </h4>
                                                            <div class="row g-2">
                                                                <div class="col-6 col-md-3">
                                                                    <small class="text-muted d-block">Quantité totale:</small>
                                                                    <span class="fw-medium"><?= htmlspecialchars($besoin['quantite'] ?? '0') ?> <?= htmlspecialchars($besoin['unite_symbole'] ?? '') ?></span>
                                                                </div>
                                                                <div class="col-6 col-md-3">
                                                                    <small class="text-muted d-block">Déjà reçu:</small>
                                                                    <span class="fw-medium"><?= htmlspecialchars($besoin['quantite_recue'] ?? 0) ?> <?= htmlspecialchars($besoin['unite_symbole'] ?? '') ?></span>
                                                                </div>
                                                                <div class="col-6 col-md-3">
                                                                    <small class="text-muted d-block">Restant:</small>
                                                                    <span class="fw-bold text-danger"><?= htmlspecialchars($besoin['quantite_restante'] ?? 0) ?> <?= htmlspecialchars($besoin['unite_symbole'] ?? '') ?></span>
                                                                </div>
                                                            </div>

                                                            <div class="progress mt-3" style="height: 8px;">
                                                                <div class="progress-bar bg-success" style="width: <?= $pourcentageRempli ?>%;"
                                                                    role="progressbar" aria-valuenow="<?= $pourcentageRempli ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                                            </div>
                                                        </div>

                                                        <div class="ms-lg-3 mt-3 mt-lg-0">
                                                            <?php
                                                            $restant = (int)($besoin['quantite_restante'] ?? 0);
                                                            if ($restant > 100): ?>
                                                                <span class="badge bg-danger"><i class="fa fa-exclamation-circle me-1"></i> URGENT</span>
                                                            <?php elseif ($restant > 50): ?>
                                                                <span class="badge bg-warning text-dark"><i class="fa fa-clock-o me-1"></i> Moyen</span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success"><i class="fa fa-check me-1"></i> Faible</span>
                                                            <?php endif; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Paramètres de vente -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <div class="d-flex align-items-center">
                                    <strong class="me-3"><i class="fa fa-percent me-2"></i> Pourcentage de retenue actuel:</strong>
                                    <span class="h5 text-success mb-0 me-3"><?= htmlspecialchars($pourcentage ?? '10') ?>%</span>
                                </div>

                                <form method="POST" action="/vente/pourcentage" class="d-flex gap-2">
                                    <input type="number" name="pourcentage" min="1" max="100"
                                        value="<?= htmlspecialchars($pourcentage ?? '10') ?>"
                                        class="form-control form-control-sm" style="width: 80px;">
                                    <button type="submit" class="btn btn-info btn-sm"><i class="fa fa-edit me-1"></i> Modifier</button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Filtres et liste des dons -->
                    <h2 class="h4 mb-3"><i class="fa fa-cubes me-2"></i> Dons disponibles à la vente</h2>

                    <div class="card mb-4">
                        <div class="card-body">
                            <div class="d-flex flex-wrap align-items-center gap-3">
                                <label for="villeFilter" class="fw-bold mb-0"><i class="fa fa-filter me-2"></i> Filtrer par ville :</label>
                                <select id="villeFilter" class="form-select form-select-sm" style="width: auto; min-width: 200px;">
                                    <option value="">Toutes les villes</option>
                                    <?php foreach ($villes as $ville): ?>
                                        <option value="<?= $ville['id'] ?>"><?= htmlspecialchars($ville['nom']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <span class="text-muted small" id="donCount"></span>
                            </div>
                        </div>
                    </div>

                    <div class="vstack gap-3" id="donsList">
                        <?php if (empty($donsAVendre)): ?>
                            <div class="card">
                                <div class="card-body text-center py-5">
                                    <p class="h5 text-muted mb-3"><i class="fa fa-inbox fa-2x mb-3 d-block text-muted"></i> Aucun don disponible à la vente</p>
                                    <p class="text-muted">Tous les dons disponibles ont été vendus ou sont déjà en argent.</p>
                                </div>
                            </div>
                        <?php else: ?>
                            <?php foreach ($donsAVendre as $don):
                                $montantVente = (float)($don['valeur_estimee'] ?? 1000);
                                $montantRecupere = $montantVente * (100 - (float)($pourcentage ?? 10)) / 100;
                                $aBesoins = (isset($don['besoins_correspondants']) && $don['besoins_correspondants'] > 0);
                            ?>
                                <div class="card <?= $aBesoins ? 'border-warning' : '' ?>" data-ville="<?= $don['idville_attribuee'] ?? '' ?>">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap">
                                            <div class="flex-grow-1">
                                                <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                                                    <h3 class="h5 fw-bold mb-0">
                                                        <?= htmlspecialchars($don['description'] ?? 'Sans description') ?>
                                                    </h3>

                                                    <?php if (!empty($don['valeur'])): ?>
                                                        <span class="badge bg-info"><i class="fa fa-tag me-1"></i> Prix fixé: <?= number_format((float)$don['valeur'], 0, ',', ' ') ?> Ar</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-warning text-dark"><i class="fa fa-line-chart me-1"></i> Estimation</span>
                                                    <?php endif; ?>

                                                    <!-- Indicateur de besoins correspondants (info seulement) -->
                                                    <?php if ($aBesoins): ?>
                                                        <span class="badge bg-secondary text-dark">
                                                            <i class="fa fa-exclamation-triangle me-1"></i> <?= $don['besoins_correspondants'] ?> besoin(s) non satisfait(s)
                                                        </span>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block"><i class="fa fa-cubes me-1"></i> Quantité:</small>
                                                        <span class="fw-medium"><?= htmlspecialchars($don['quantite'] ?? '0') ?> <?= htmlspecialchars($don['unite_symbole'] ?? '') ?></span>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block"><i class="fa fa-money me-1"></i> Valeur estimée:</small>
                                                        <span class="fw-medium"><?= number_format((float)($don['valeur_estimee'] ?? 0), 0, ',', ' ') ?> Ar</span>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block"><i class="fa fa-map-marker me-1"></i> Ville:</small>
                                                        <span class="fw-medium"><?= htmlspecialchars($don['ville_attribuee'] ?: 'Non assignée') ?></span>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <small class="text-muted d-block"><i class="fa fa-tags me-1"></i> Catégorie:</small>
                                                        <span class="fw-medium"><?= htmlspecialchars($don['categorie_nom'] ?: 'Non catégorisé') ?></span>
                                                    </div>
                                                </div>

                                                <div class="mt-3 p-3 bg-light rounded">
                                                    <div class="d-flex justify-content-between align-items-center flex-wrap">
                                                        <span class="text-success fw-bold h6 mb-0">
                                                            <i class="fa fa-money text-success me-2"></i> Valeur après vente: <?= number_format($montantRecupere, 0, ',', ' ') ?> Ar
                                                        </span>
                                                        <small class="text-muted">(après déduction <?= htmlspecialchars($pourcentage ?? '10') ?>%)</small>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="ms-lg-3 mt-3 mt-lg-0">
                                                <a href="/vente/vendre/<?= $don['id'] ?>"
                                                    class="btn <?= $aBesoins ? 'btn-outline-danger' : 'btn-success' ?> btn-vendre"
                                                    data-montant="<?= number_format($montantRecupere, 0, ',', ' ') ?> Ar"
                                                    data-id="<?= $don['id'] ?>">
                                                    <?php if ($aBesoins): ?>
                                                        <i class="fa fa-exclamation-triangle me-1"></i> Vendre quand même
                                                    <?php else: ?>
                                                        <i class="fa fa-money me-1"></i> Vendre
                                                    <?php endif; ?>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Historique des ventes (optionnel) -->
                    <?php if (!empty($historique)): ?>
                        <h2 class="h4 mt-5 mb-3"><i class="fa fa-history me-2"></i> Historique des ventes</h2>
                        <div class="card">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="ps-3"><i class="fa fa-calendar me-1"></i> Date</th>
                                                <th><i class="fa fa-cube me-1"></i> Don vendu</th>
                                                <th><i class="fa fa-map-marker me-1"></i> Ville</th>
                                                <th class="text-end"><i class="fa fa-money me-1"></i> Montant vente</th>
                                                <th class="text-end"><i class="fa fa-money text-success me-1"></i> Montant récupéré</th>
                                                <th class="text-center"><i class="fa fa-percent me-1"></i> Pourcentage</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($historique as $vente): ?>
                                                <tr>
                                                    <td class="ps-3"><i class="fa fa-clock-o me-2 text-muted"></i><?= date('d/m/Y H:i', strtotime($vente['date_vente'] ?? 'now')) ?></td>
                                                    <td><?= htmlspecialchars($vente['don_description'] ?? 'N/A') ?></td>
                                                    <td><?= htmlspecialchars($vente['ville_attribuee'] ?: 'N/A') ?></td>
                                                    <td class="text-end"><?= number_format((float)($vente['montant_vente'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                                    <td class="text-end text-success fw-bold"><?= number_format((float)($vente['montant_recupere'] ?? 0), 0, ',', ' ') ?> Ar</td>
                                                    <td class="text-center"><?= htmlspecialchars($vente['pourcentage_applique'] ?? '0') ?>%</td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php Flight::render('./inc/footer.php'); ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Filtre par ville
            const villeFilter = document.getElementById('villeFilter');
            const cards = document.querySelectorAll('.card[data-ville]'); // Plus sélectif: uniquement les cartes avec data-ville
            const donCount = document.getElementById('donCount');

            function updateDonCount() {
                const visibleCards = Array.from(cards).filter(card => card.style.display !== 'none');
                if (donCount) {
                    donCount.innerHTML = '<i class="fa fa-eye me-1"></i>' + visibleCards.length + ' don(s) visible(s)';
                }
            }

            if (villeFilter) {
                villeFilter.addEventListener('change', function() {
                    const villeId = this.value;
                    let visibleCount = 0;

                    cards.forEach(card => {
                        const cardVille = card.getAttribute('data-ville');

                        if (!villeId || cardVille === villeId) {
                            card.style.display = 'block';
                            visibleCount++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (donCount) {
                        donCount.innerHTML = '<i class="fa fa-eye me-1"></i>' + visibleCount + ' don(s) visible(s)';
                    }
                });
            }

            // Vérification AJAX pour les boutons de vente
            const vendreBtns = document.querySelectorAll('.btn-vendre');

            vendreBtns.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    const url = this.href;
                    const donId = this.dataset.id;
                    const montantRecupere = this.dataset.montant;

                    // Sauvegarder le contenu original (incluant les icônes)
                    const originalContent = this.innerHTML;

                    // Désactiver le bouton pendant la vérification
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Vérification...';
                    this.classList.add('disabled');

                    // Vérifier si le don peut être vendu
                    fetch('/api/vente/verifier/' + donId)
                        .then(response => response.json())
                        .then(data => {
                            // Réactiver le bouton
                            this.disabled = false;
                            this.innerHTML = originalContent;
                            this.classList.remove('disabled');

                            if (data.peut_vendre) {
                                // Si la vente est possible, confirmer et rediriger
                                if (confirm('💰 Vendre ce don pour ' + montantRecupere + ' ?')) {
                                    window.location.href = url;
                                }
                            } else {
                                // Afficher un message d'erreur avec la liste des besoins
                                if (data.besoins && data.besoins.length > 0) {
                                    // Rediriger vers la page avec les détails des besoins
                                    const besoinsJson = encodeURIComponent(JSON.stringify(data.besoins));
                                    window.location.href = '/vente?error=' + encodeURIComponent(data.message) + '&besoins=' + besoinsJson + '&don_id=' + donId;
                                } else {
                                    // Utiliser SweetAlert si disponible, sinon alert standard
                                    if (typeof Swal !== 'undefined') {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Vente impossible',
                                            text: data.message,
                                            confirmButtonColor: '#dc3545'
                                        });
                                    } else {
                                        alert('❌ ' + data.message);
                                    }
                                }
                            }
                        })
                        .catch(error => {
                            console.error('Erreur:', error);

                            // Réactiver le bouton
                            this.disabled = false;
                            this.innerHTML = originalContent;
                            this.classList.remove('disabled');

                            // En cas d'erreur, procéder avec confirmation
                            if (confirm('⚠️ Erreur de vérification. Vendre quand même pour ' + montantRecupere + ' Ar ?')) {
                                window.location.href = url;
                            }
                        });
                });
            });

            // Scroll automatique vers la section des besoins si elle existe
            const besoinsSection = document.getElementById('besoinsSection');
            if (besoinsSection) {
                // Ajouter un indicateur visuel
                const header = besoinsSection.querySelector('.card-header');
                if (header) {
                    header.classList.add('bg-danger', 'text-white');
                }

                // Scroll avec offset pour la navbar
                setTimeout(() => {
                    const offset = 20;
                    const elementPosition = besoinsSection.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;

                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }, 100);
            }

            // Initialisation du compteur
            updateDonCount();

            // Ajouter des tooltips Bootstrap si disponibles
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
                tooltipTriggerList.map(function(tooltipTriggerEl) {
                    return new bootstrap.Tooltip(tooltipTriggerEl);
                });
            }

            console.log('✅ Page de vente initialisée avec ' + vendreBtns.length + ' boutons de vente');
        });
    </script>
</body>

</html>