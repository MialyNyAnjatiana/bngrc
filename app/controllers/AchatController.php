<?php

namespace app\controllers;

use app\models\AchatModel;
use app\models\DonsModel;
use Flight;

class AchatController {
    
    public function showAchatPage() {
        $db = Flight::db();
        $achatModel = new AchatModel($db);
        
        // Récupérer toutes les villes pour le filtre
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Par défaut, on montre tous les dons disponibles
        $donsArgent = $achatModel->getDonsArgentDisponibles();
        $besoins = $achatModel->getBesoinsAchetables();
        $achats = $achatModel->getAchats();
        $statsVilles = $achatModel->getTotalAchatsParVille();
        
        Flight::render('achat', [
            'donsArgent' => $donsArgent,
            'besoins' => $besoins,
            'villes' => $villes,
            'achats' => $achats,
            'statsVilles' => $statsVilles,
            'villeSelectionnee' => null
        ]);
    }
    
    public function effectuerAchat() {
        $iddon = $_POST['iddon'] ?? null;
        $idbesoin = $_POST['idbesoin'] ?? null;
        $quantite = $_POST['quantite'] ?? 0;
        
        if (!$iddon || !$idbesoin || $quantite <= 0) {
            Flight::redirect('/achat?error=' . urlencode('Veuillez remplir tous les champs'));
            return;
        }
        
        $model = new AchatModel(Flight::db());
        $resultat = $model->effectuerAchat($iddon, $idbesoin, $quantite);
        
        if ($resultat['success']) {
            Flight::redirect('/achat?success=1&message=' . urlencode($resultat['message']));
        } else {
            Flight::redirect('/achat?error=' . urlencode($resultat['message']));
        }
    }
    
    public function getDonsParVille($idVille) {
        $model = new AchatModel(Flight::db());
        $dons = $model->getDonsArgentDisponibles($idVille);
        Flight::json($dons);
    }
    
    public function getBesoinsParVille($idVille) {
        $model = new AchatModel(Flight::db());
        $besoins = $model->getBesoinsAchetables($idVille);
        Flight::json($besoins);
    }
    
    public function filtrerParVille($idVille) {
        $db = Flight::db();
        $achatModel = new AchatModel($db);
        
        // Récupérer les données filtrées par ville
        $donsArgent = $achatModel->getDonsArgentDisponibles($idVille);
        $besoins = $achatModel->getBesoinsAchetables($idVille);
        $achats = $achatModel->getAchats($idVille);
        $statsVilles = $achatModel->getTotalAchatsParVille();
        
        // Récupérer toutes les villes pour le filtre
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer le nom de la ville sélectionnée
        $villeNom = "";
        foreach ($villes as $ville) {
            if ($ville['id'] == $idVille) {
                $villeNom = $ville['nom'];
                break;
            }
        }
        
        Flight::render('achat', [
            'donsArgent' => $donsArgent,
            'besoins' => $besoins,
            'villes' => $villes,
            'achats' => $achats,
            'statsVilles' => $statsVilles,
            'villeSelectionnee' => $idVille,
            'villeNom' => $villeNom
        ]);
    }
}