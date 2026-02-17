<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Accueil</title>
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
                    <!-- Header -->
                    <div class="text-center mb-5">
                        <h2 class="fw-bold">Liste des besoins par ville</h2>
                        <p class="text-secondary">Suivi des besoins et des dons attribués</p>
                    </div>

                    <div class="vstack gap-4">
                        <?php foreach ($villes as $villeId => $data): ?>
                            <div class="card shadow-sm border-0">
                                <!-- Ville Header -->
                                <div class="card-header bg-dark py-3 d-flex flex-wrap justify-content-between align-items-center">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="bi bi-geo-alt-fill text-primary"></i>
                                        <h5 class="fw-semibold mb-0 text-white"><?= htmlspecialchars($data['ville']['nom']) ?></h5>
                                    </div>
                                    <span class="badge bg-light text-dark px-3 py-2 rounded-pill">
                                        <i class="fa fa-money me-1 text-success"></i>
                                        <?= number_format($data['depenses'] ?? 0, 0, ',', ' ') ?> Ar
                                    </span>
                                </div>

                                <div class="card-body">
                                    <!-- Besoins -->
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="bi bi-clipboard-check text-primary"></i>
                                        <h6 class="fw-semibold mb-0">Besoins</h6>
                                        <span class="badge bg-danger text-dark rounded-pill ms-2 px-3">
                                            <?= count($data['besoins']) ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($data['besoins'])): ?>
                                        <div class="row g-2 mb-4">
                                            <?php foreach ($data['besoins'] as $besoin): ?>
                                                <div class="col-md-6 col-lg-4">
                                                    <div class="p-3 bg-light rounded-3 h-100">
                                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                                            <span class="fw-medium"><?= htmlspecialchars($besoin['description']) ?></span>
                                                            <?php if (($besoin['quantite_recue'] ?? 0) < $besoin['quantite']): ?>
                                                                <span class="badge bg-warning bg-opacity-15 text-black rounded-pill px-3">
                                                                    <i class="fa fa-clock me-1"></i>En attente
                                                                </span>
                                                            <?php else: ?>
                                                                <span class="badge bg-success bg-opacity-15 text-black rounded-pill px-3">
                                                                    <i class="fa fa-check me-1"></i>Attribué
                                                                </span>
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="small text-secondary">
                                                            <div>Total: <span class="text-dark"><?= $besoin['quantite'] ?> <?= $besoin['unite_symbole'] ?? '' ?></span></div>
                                                            <div>Reçu: <span class="text-info"><?= $besoin['quantite_recue'] ?? 0 ?> <?= $besoin['unite_symbole'] ?? '' ?></span></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-secondary text-center py-3 mb-4">Aucun besoin enregistré</p>
                                    <?php endif; ?>

                                    <!-- Dons -->
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <i class="bi bi-gift text-success"></i>
                                        <h6 class="fw-semibold mb-0">Dons attribués</h6>
                                        <span class="badge bg-success text-dark rounded-pill ms-2 px-3">
                                            <?= count($data['dons']) ?>
                                        </span>
                                    </div>

                                    <?php if (!empty($data['dons'])): ?>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-borderless align-middle mb-0">
                                                <tbody>
                                                    <?php foreach ($data['dons'] as $don): ?>
                                                        <tr>
                                                            <td class="ps-0">
                                                                <i class="bi bi-dot me-1 text-success"></i>
                                                                <?= htmlspecialchars($don['description']) ?>
                                                            </td>
                                                            <td class="text-end pe-0 text-secondary">
                                                                <?= htmlspecialchars($don['quantite']) ?> <?= htmlspecialchars($don['unite_symbole'] ?? '') ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-secondary text-center py-3">Aucun don attribué</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php Flight::render('./inc/footer.php'); ?>
    </div>
    <!-- page-body-wrapper ends -->

    <!-- container-scroller -->


</body>

</html>