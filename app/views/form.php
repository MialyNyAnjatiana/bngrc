<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Formulaire ajout de <?= ($action == "don") ? 'don' : 'besoin'; ?></title>
	<!-- plugins:css -->
	<link rel="stylesheet" href="/assets/vendors/mdi/css/materialdesignicons.min.css">
	<link rel="stylesheet" href="/assets/vendors/ti-icons/css/themify-icons.css">
	<link rel="stylesheet" href="/assets/vendors/css/vendor.bundle.base.css">
	<link rel="stylesheet" href="/assets/vendors/font-awesome/css/font-awesome.min.css">
	<!-- endinject -->
	<!-- Plugin css for this page -->
	<link rel="stylesheet" href="/assets/vendors/font-awesome/css/font-awesome.min.css" />
	<link rel="stylesheet" href="/assets/vendors/bootstrap-datepicker/bootstrap-datepicker.min.css">
	<!-- End plugin css for this page -->
	<!-- inject:css -->
	<!-- endinject -->
	<!-- Layout styles -->
	<link rel="stylesheet" href="/assets/css/style.css">
	<!-- End layout styles -->
	<link rel="shortcut icon" href="/assets/images/favicon.png" />
</head>

<body>
	<div class="container-scroller">
		<?php Flight::render('./inc/navbar.php'); ?>

		<div class="container-fluid page-body-wrapper full-page-wrapper">
			<div class="content-wrapper d-flex align-items-center auth">
				<div class="row flex-grow">
					<div class="col-lg-6 mx-auto">
						<div class="auth-form-light text-left p-5">
							<div class="card-body">
								<h4 class="card-title">Ajouter un <?= ($action == "don") ? 'don' : 'besoin'; ?></h4>

								<?php if (isset($_GET['success'])): ?>
									<div class="alert alert-success"><?= htmlspecialchars($_GET['success']) ?></div>
								<?php endif; ?>

								<?php if (isset($_GET['error'])): ?>
									<div class="alert alert-danger"><?= htmlspecialchars($_GET['error']) ?></div>
								<?php endif; ?>

								<form class="forms-sample" action="<?= ($action == "don") ? '/dons/sans-besoin' : '/besoin/add'; ?>" method="POST">
									<div class="form-group">
										<label for="description">Description :</label>
										<input type="text" class="form-control" name="description" id="description" required
											placeholder="Ex: Riz, Argent, Tôles...">
									</div>

									<div class="form-group">
										<label for="id_categorie">Catégorie (optionnel) :</label>
										<select name="id_categorie" id="id_categorie" class="form-select">
											<option value="">Sélectionnez une catégorie</option>
											<?php foreach ($categories as $categorie): ?>
												<option value="<?= $categorie['id'] ?>">
													<?= htmlspecialchars($categorie['nom']) ?>
												</option>
											<?php endforeach; ?>
										</select>
									</div>

<div class="form-group">
    <label for="quantite">Quantité :</label>
    <input type="number" class="form-control" name="quantite" id="quantite" step="0.01" min="0.01" required>
</div>

<div class="form-group">
    <label for="prix_unitaire">Prix unitaire (Ar) :</label>
    <input type="number" class="form-control" name="prix_unitaire" id="prix_unitaire" 
           step="100" min="0" placeholder="Prix en Ariary">
    <small class="form-text text-muted">
        <i class="fa fa-info-circle"></i> Laissez vide pour une estimation automatique basée sur la catégorie.<br>
        <strong>La valeur estimée du don sera : prix unitaire × quantité</strong>
    </small>
</div>

<div class="form-group">
    <label for="id_unite">Unité :</label>
    <select name="id_unite" id="id_unite" class="form-select" required>
        <option value="">Sélectionnez une unité</option>
        <?php foreach ($unites as $unite): ?>
            <option value="<?= $unite['id'] ?>" data-type="<?= $unite['type'] ?>">
                <?= htmlspecialchars($unite['nom']) ?> (<?= htmlspecialchars($unite['symbole']) ?>) - <?= htmlspecialchars($unite['type']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>


									<div class="form-group">
										<label for="idVille">Ville d'attribution * :</label>
										<select name="idVille" id="idVille" class="form-select" required>
											<option value="">Sélectionnez une ville</option>
											<?php foreach ($villes as $ville): ?>
												<option value="<?= $ville['id'] ?>">
													<?= htmlspecialchars($ville['nom']) ?> (<?= htmlspecialchars($ville['region']) ?>)
												</option>
											<?php endforeach; ?>
										</select>
										<?php if ($action == "don") { ?>
											<small class="form-text text-muted">
												* Les dons ne peuvent être utilisés que dans la ville où ils sont attribués
											</small>
										<?php } ?>
									</div>

									<button type="submit" class="btn btn-gradient-success me-2">Ajouter le <?= ($action == "don") ? 'don' : 'besoin'; ?></button>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>

		</div>
		<?php Flight::render('./inc/footer.php'); ?>
	</div>

	<script>
		document.getElementById('id_unite').addEventListener('change', function() {
			const selectedOption = this.options[this.selectedIndex];
			const type = selectedOption.dataset.type;

			if (type === 'monnaie') {
				const categorieSelect = document.getElementById('id_categorie');
				for (let i = 0; i < categorieSelect.options.length; i++) {
					if (categorieSelect.options[i].text.toLowerCase().includes('argent')) {
						categorieSelect.selectedIndex = i;
						break;
					}
				}
			}
		});
	</script>

	<!-- page-body-wrapper ends -->

	<!-- container-scroller -->
</body>

</html>