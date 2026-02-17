<?php

namespace app\controllers;

use app\models\DonsModel;
use Flight;
use PDO;

class DonsController {

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getDons($idVille) {
        $model = new DonsModel(Flight::db());
        $dons = $model->getDonsNonLiesParVille($idVille);

        Flight::render('home', ['dons' => $dons]);
    }

    public function showFormDonSansBesoin() {
        $db = Flight::db();
        $model = new DonsModel($db);
        
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
        $categories = $model->getCategories();
        $unites = $model->getUnites();

        Flight::render('form', [
            'villes' => $villes,
            'categories' => $categories,
            'unites' => $unites,
            'action' => 'don'
        ]);
    }

public function storeDonSansBesoin() {
    $description = $_POST['description'] ?? '';
    $quantite = $_POST['quantite'] ?? 0;
    $prixUnitaire = $_POST['prix_unitaire'] ?? null;
    $idUnite = $_POST['id_unite'] ?? null;
    $idVille = $_POST['idVille'] ?? null;
    $idCategorie = $_POST['id_categorie'] ?? null;

    if (!$description || !$quantite || !$idUnite || !$idVille) {
        Flight::redirect('/dons/sans-besoin?error=' . urlencode('Veuillez remplir tous les champs obligatoires'));
        return;
    }

    // Si prix unitaire est vide, on le met à null
    if (empty($prixUnitaire)) {
        $prixUnitaire = null;
    }

    $model = new DonsModel(Flight::db());
    $result = $model->ajouterDonSansBesoin($description, $quantite, $idUnite, $idVille, $idCategorie, $prixUnitaire);

    if ($result) {
        Flight::redirect('/liaison?success=' . urlencode('Don ajouté avec succès'));
    } else {
        Flight::redirect('/dons/sans-besoin?error=' . urlencode('Erreur lors de l\'ajout du don'));
    }
}

    public function lierDon() {
        $idDon = $_POST['id_don'] ?? null;
        $idBesoin = $_POST['id_besoin'] ?? null;

        if (!$idDon || !$idBesoin) {
            Flight::redirect('/liaison?error=' . urlencode('Veuillez sélectionner un don et un besoin'));
            return;
        }

        $model = new DonsModel(Flight::db());
        $resultat = $model->lierDonABesoin($idDon, $idBesoin);

        if ($resultat['success']) {
            Flight::redirect('/liaison?success=1&message=' . urlencode($resultat['message']));
        } else {
            Flight::redirect('/liaison?error=' . urlencode($resultat['message']));
        }
    }

    public function getUnitesByType($type) {
        $model = new DonsModel(Flight::db());
        $unites = $model->getUnitesByType($type);
        Flight::json($unites);
    }
}