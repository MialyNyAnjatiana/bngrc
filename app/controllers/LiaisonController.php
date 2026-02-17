<?php
// app/controllers/LiaisonController.php

namespace app\controllers;

use app\models\DonsModel;
use Flight;

class LiaisonController {
    
    public function showLiaisonPage() {
        $db = Flight::db();
        $model = new DonsModel($db);
        
        // Récupérer les dons non liés avec toutes les informations
        $donsNonLies = $model->getDonsNonLies();
        
        // Récupérer toutes les villes
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer tous les besoins non satisfaits avec unités et catégories
        $besoins = $db->query("
            SELECT 
                b.*, 
                v.nom as nom_ville,
                u.nom as unite_nom,
                u.symbole as unite_symbole,
                u.type as unite_type,
                c.nom as categorie_nom,
                (b.quantite - COALESCE(b.quantite_recue, 0)) as quantite_restante
            FROM besoin b
            JOIN ville v ON b.idville = v.id
            JOIN unite u ON b.id_unite = u.id
            JOIN categorie c ON b.idcategorie = c.id
            WHERE b.quantite > COALESCE(b.quantite_recue, 0)
            ORDER BY v.nom, b.description
        ")->fetchAll(\PDO::FETCH_ASSOC);
        
        Flight::render('liaison', [
            'donsNonLies' => $donsNonLies,
            'besoins' => $besoins,
            'villes' => $villes
        ]);
    }

    /**
     * Méthode simplifiée pour afficher la page de liaison
     */
    public function showLiaisonSimple() {
        $db = Flight::db();
        $model = new DonsModel($db);
        
        // Récupérer les dons non liés
        $donsNonLies = $model->getDonsNonLies();
        
        // Récupérer toutes les villes
        $villes = $db->query("SELECT * FROM ville ORDER BY nom")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Récupérer tous les besoins non satisfaits
        $besoins = $db->query("
            SELECT 
                b.*, 
                v.nom as nom_ville,
                u.nom as unite_nom,
                u.symbole as unite_symbole,
                u.type as unite_type,
                c.nom as categorie_nom,
                (b.quantite - COALESCE(b.quantite_recue, 0)) as quantite_restante
            FROM besoin b
            JOIN ville v ON b.idville = v.id
            JOIN unite u ON b.id_unite = u.id
            JOIN categorie c ON b.idcategorie = c.id
            WHERE b.quantite > COALESCE(b.quantite_recue, 0)
            ORDER BY v.nom, b.description
        ")->fetchAll(\PDO::FETCH_ASSOC);
        
        // Debug : Vérifier les données
        error_log("LiaisonSimple - Dons non liés: " . count($donsNonLies));
        error_log("LiaisonSimple - Besoins: " . count($besoins));
        
        Flight::render('liaison', [
            'donsNonLies' => $donsNonLies,
            'besoins' => $besoins,
            'villes' => $villes
        ]);
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
    
    public function getBesoinsParVille($idVille) {
        $db = Flight::db();
        $stmt = $db->prepare("
            SELECT 
                b.*, 
                v.nom as nom_ville,
                u.nom as unite_nom,
                u.symbole as unite_symbole,
                u.type as unite_type,
                c.nom as categorie_nom,
                (b.quantite - COALESCE(b.quantite_recue, 0)) as quantite_restante
            FROM besoin b
            JOIN ville v ON b.idville = v.id
            JOIN unite u ON b.id_unite = u.id
            JOIN categorie c ON b.idcategorie = c.id
            WHERE b.idville = ? AND b.quantite > COALESCE(b.quantite_recue, 0)
            ORDER BY b.description
        ");
        $stmt->execute([$idVille]);
        $besoins = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        
        Flight::json($besoins);
    }

    /**
     * API pour récupérer les besoins financiers
     */
    public function getBesoinsFinanciers() {
        $db = Flight::db();
        $idVille = $_GET['idVille'] ?? null;
        
        $model = new DonsModel($db);
        $besoins = $model->getBesoinsFinanciersNonSatisfaits($idVille);
        
        Flight::json($besoins);
    }
}