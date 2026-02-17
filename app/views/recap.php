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
    <div class="container-scroller">
        <?php Flight::render('./inc/navbar.php'); ?>
        <div class="container-fluid page-body-wrapper full-page-wrapper">
            <div class="content-wrapper p-5">
                <div class="row">
                    <!-- Besoins Totaux -->
                    <div class="col-md-3 stretch-card grid-margin">
                        <div class="card bg-gradient-primary card-img-holder text-white">
                            <div class="card-body">
                                <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                <h4 class="font-weight-normal mb-3">
                                    Besoins Totaux
                                    <i class="mdi mdi-chart-line mdi-24px float-end"></i>
                                </h4>
                                <h2 class="mb-4" id="valBesoin">Chargement...</h2>
                                <h6 class="card-text">Valeur monétaire des besoins</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Besoins Satisfaits -->
                    <div class="col-md-3 stretch-card grid-margin">
                        <div class="card bg-gradient-success card-img-holder text-white">
                            <div class="card-body">
                                <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                <h4 class="font-weight-normal mb-3">
                                    Besoins Satisfaits
                                    <i class="mdi mdi-check-circle mdi-24px float-end"></i>
                                </h4>
                                <h2 class="mb-4" id="valSatisfait">Chargement...</h2>
                                <h6 class="card-text">Total comblé (Nature + Achats)</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Dons Reçus -->
                    <div class="col-md-3 stretch-card grid-margin">
                        <div class="card bg-gradient-info card-img-holder text-white">
                            <div class="card-body">
                                <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                <h4 class="font-weight-normal mb-3">
                                    Dons Reçus
                                    <i class="mdi mdi-gift mdi-24px float-end"></i>
                                </h4>
                                <h2 class="mb-4" id="valRecu">Chargement...</h2>
                                <h6 class="card-text">Valeur totale des dons</h6>
                            </div>
                        </div>
                    </div>

                    <!-- Dons Dispatchés -->
                    <div class="col-md-3 stretch-card grid-margin">
                        <div class="card bg-gradient-danger card-img-holder text-white">
                            <div class="card-body">
                                <img src="assets/images/dashboard/circle.svg" class="card-img-absolute" alt="circle-image" />
                                <h4 class="font-weight-normal mb-3">
                                    Dons Dispatchés
                                    <i class="mdi mdi-truck mdi-24px float-end"></i>
                                </h4>
                                <h2 class="mb-4" id="valDispatche">Chargement...</h2>
                                <h6 class="card-text">Valeur des dons utilisés</h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Refresh button -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Récapitulatif Global</h2>
                    <button id="refreshBtn" class="btn btn-primary">Actualiser (AJAX)</button>
                </div>

                <!-- Table -->
                <div class="card">
                    <div class="card-body">
                        <h3 class="card-title">Détails par Catégorie</h3>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Catégorie</th>
                                        <th>Besoins Totaux</th>
                                        <th>Satisfaits</th>
                                        <th>Dons Reçus</th>
                                        <th>Dispatchés</th>
                                    </tr>
                                </thead>
                                <tbody id="categoryStatsBody">
                                    
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php Flight::render('./inc/footer.php'); ?>
    </div>

    <script>
        function formatMoney(amount) {
            return new Intl.NumberFormat('fr-MG', {
                style: 'currency',
                currency: 'MGA',
                maximumFractionDigits: 0
            }).format(amount).replace('MGA', 'Ar');
        }

        function updateCategoryTable(categories) {
            const tbody = document.getElementById('categoryStatsBody');
            tbody.innerHTML = '';

            if (!categories || categories.length === 0) {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;">Aucune donnée par catégorie disponible.</td></tr>';
                return;
            }

            categories.forEach(cat => {
                const row = document.createElement('tr');
                row.innerHTML = `
                <td><strong>${cat.nom}</strong></td>
                <td>${formatMoney(cat.besoins_totaux)}</td>
                <td>${formatMoney(cat.besoins_satisfaits)}</td>
                <td>${formatMoney(cat.dons_recus)}</td>
                <td>${formatMoney(cat.dons_dispatches)}</td>
            `;
                tbody.appendChild(row);
            });
        }

        function loadData() {
            const btn = document.getElementById('refreshBtn');
            btn.disabled = true;
            btn.textContent = 'Chargement...';

            fetch('/recap/data')
                .then(response => response.json())
                .then(data => {

                    document.getElementById('valBesoin').textContent = formatMoney(data.besoins_totaux);
                    document.getElementById('valSatisfait').textContent = formatMoney(data.besoins_satisfaits);
                    document.getElementById('valRecu').textContent = formatMoney(data.dons_recus);
                    document.getElementById('valDispatche').textContent = formatMoney(data.dons_dispatches);


                    if (data.categories) {
                        updateCategoryTable(data.categories);
                    }
                })
                .catch(err => console.error(err))
                .finally(() => {
                    btn.disabled = false;
                    btn.textContent = 'Actualiser';
                });
        }

        document.getElementById('refreshBtn').addEventListener('click', loadData);

        // Charger au démarrage
        loadData();
    </script>
</body>