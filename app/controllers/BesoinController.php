<?php

namespace app\controllers;

use Flight;
use app\models\BesoinModel;
use app\models\DonsModel;
use PDO;

class BesoinController
{
    public static function list()
    {
        $BesoinModel = new BesoinModel(Flight::db());
        $besoins = $BesoinModel->all();
        Flight::render('besoin_list', compact('besoins'));
    }

    public static function form()
    {
        $db = Flight::db();
        $model = new DonsModel($db);
        
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(PDO::FETCH_ASSOC);
        $categories = $model->getCategories();
        $unites = $model->getUnites();

        Flight::render('form', [
            'villes' => $villes, 
            'categories' => $categories, 
            'unites' => $unites,
            'action' => 'besoin'
        ]);
    }

    public static function add()
    {
        // Récupération des données du formulaire
        $description = $_POST['description'] ?? '';
        $quantite = $_POST['quantite'] ?? 0;
        $prixUnitaire = $_POST['prix_unitaire'] ?? null;
        $idUnite = $_POST['id_unite'] ?? null;
        $idVille = $_POST['idVille'] ?? null;
        $idCategorie = $_POST['id_categorie'] ?? null;

        // Validation des champs obligatoires
        if (!$description || !$quantite || !$idUnite || !$idVille) {
            Flight::redirect('/besoin/form?error=' . urlencode('Veuillez remplir tous les champs obligatoires'));
            return;
        }

        // Si prix unitaire est vide, le mettre à null
        if (empty($prixUnitaire)) {
            $prixUnitaire = null;
        }

        $BesoinModel = new BesoinModel(Flight::db());
        
        // CORRECTION: Ordre correct des paramètres
        $result = $BesoinModel->insert(
            $idVille, 
            $idCategorie, 
            $description, 
            $quantite, 
            $idUnite,
            $prixUnitaire  // Ajout du prix unitaire
        );

        if ($result) {
            Flight::redirect('/home?success=' . urlencode('Besoin ajouté avec succès'));
        } else {
            Flight::redirect('/besoin/form?error=' . urlencode('Erreur lors de l\'ajout du besoin'));
        }
    }
}