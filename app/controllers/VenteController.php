<?php
// app/controllers/VenteController.php

namespace app\controllers;

use app\models\VenteModel;
use app\models\ParametresModel;

require_once __DIR__ . '/../models/ParametresModel.php';
require_once __DIR__ . '/../models/VenteModel.php';

use Flight;

class VenteController {
    
    /**
     * Affiche la page de vente des dons
     */
    public function showVentePage() {
        $db = Flight::db();
        $venteModel = new VenteModel($db);
        $paramModel = new ParametresModel($db);
        
        // Récupérer toutes les villes pour le filtre
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer les dons disponibles à la vente
        $donsAVendre = $venteModel->getDonsAVendre();
        
        // Récupérer l'historique des ventes
        $historique = $venteModel->getHistoriqueVentes();
        
        // Récupérer les statistiques
        $stats = $venteModel->getStatsVentesParVille();
        
        // Récupérer le pourcentage actuel
        $pourcentage = $paramModel->getValeur('pourcentage_vente', 10);
        
        Flight::render('vente', [
            'villes' => $villes,
            'donsAVendre' => $donsAVendre,
            'historique' => $historique,
            'stats' => $stats,
            'pourcentage' => $pourcentage,
            'villeSelectionnee' => null
        ]);
    }

    /**
     * Vérifie si un don peut être vendu (AJAX)
     */
    public function verifierVente($idDon) {
        header('Content-Type: application/json');
        
        try {
            $model = new VenteModel(Flight::db());
            $resultat = $model->peutVendre($idDon);
            
            echo json_encode($resultat);
        } catch (\Exception $e) {
            echo json_encode([
                'peut_vendre' => false,
                'message' => 'Erreur lors de la vérification: ' . $e->getMessage(),
                'besoins' => []
            ]);
        }
    }

    /**
     * Vente instantanée d'un don
     */
    public function vendreDonInstantanee($idDon) {
        $db = Flight::db();
        $venteModel = new VenteModel($db);
        $paramModel = new ParametresModel($db);
        
        // Récupérer le don
        $don = $venteModel->getDonById($idDon);
        
        if (!$don) {
            Flight::redirect('/vente?error=' . urlencode('Don non trouvé'));
            return;
        }
        
        // Vérifier d'abord si le don peut être vendu
        $verification = $venteModel->peutVendre($idDon);
        if (!$verification['peut_vendre']) {
            // Rediriger avec les besoins en paramètre
            $besoinsJson = urlencode(json_encode($verification['besoins']));
            Flight::redirect('/vente?error=' . urlencode($verification['message']) . '&besoins=' . $besoinsJson . '&don_id=' . $idDon);
            return;
        }
        
        // Récupérer le pourcentage
        $pourcentage = $paramModel->getValeur('pourcentage_vente', 10);
        
        // Calculer le montant de vente (valeur estimée)
        $montantVente = $don['valeur_estimee'] ?? 1000;
        
        // Effectuer la vente
        $resultat = $venteModel->vendreDon($idDon, $montantVente, 'Vente instantanée');
        
        if ($resultat['success']) {
            Flight::redirect('/vente?success=1&message=' . urlencode($resultat['message']));
        } else {
            Flight::redirect('/vente?error=' . urlencode($resultat['message']));
        }
    }

    /**
     * Met à jour le pourcentage de vente
     */
    public function mettreAJourPourcentage() {
        $pourcentage = $_POST['pourcentage'] ?? 0;
        
        if ($pourcentage <= 0 || $pourcentage > 100) {
            Flight::redirect('/vente?error=' . urlencode('Le pourcentage doit être entre 1 et 100'));
            return;
        }
        
        $model = new ParametresModel(Flight::db());
        $result = $model->setValeur('pourcentage_vente', $pourcentage, 'Pourcentage de retenue sur la vente des dons');
        
        if ($result) {
            Flight::redirect('/vente?success=1&message=' . urlencode('Pourcentage mis à jour avec succès'));
        } else {
            Flight::redirect('/vente?error=' . urlencode('Erreur lors de la mise à jour'));
        }
    }

    /**
     * Récupère l'historique des ventes par ville (AJAX)
     */
    public function getHistoriqueParVille($idVille) {
        header('Content-Type: application/json');
        $model = new VenteModel(Flight::db());
        $historique = $model->getHistoriqueVentes($idVille);
        echo json_encode($historique);
    }
}