<?php
// app/models/VenteModel.php

namespace app\models;

use PDO;

class VenteModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Vérifie si un don correspond à des besoins non satisfaits dans la même ville
     */
    public function verifierBesoinsCorrespondants($idDon) {
        // Récupérer les informations du don
        $don = $this->getDonById($idDon);
        if (!$don) {
            return [];
        }

        // Calculer la quantité restante directement dans la requête
        $sql = "SELECT 
                    b.*,
                    v.nom as ville_nom,
                    c.nom as categorie_nom,
                    u.nom as unite_nom,
                    u.symbole as unite_symbole,
                    (b.quantite - COALESCE(b.quantite_recue, 0)) as quantite_restante
                FROM besoin b
                JOIN ville v ON b.idville = v.id
                JOIN categorie c ON b.idcategorie = c.id
                JOIN unite u ON b.id_unite = u.id
                WHERE b.idville = ?
                AND b.idcategorie = ?
                AND b.id_unite = ?
                AND b.quantite > COALESCE(b.quantite_recue, 0)
                ORDER BY quantite_restante DESC";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            $don['idville_attribuee'],
            $don['idcategorie'],
            $don['id_unite']
        ]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un don peut être vendu
     */
    public function peutVendre($idDon) {
        $besoinsCorrespondants = $this->verifierBesoinsCorrespondants($idDon);
        
        if (empty($besoinsCorrespondants)) {
            return [
                'peut_vendre' => true,
                'message' => 'Ce don peut être vendu',
                'besoins' => []
            ];
        } else {
            return [
                'peut_vendre' => false,
                'message' => 'Ce don correspond à des besoins non satisfaits dans la même ville',
                'besoins' => $besoinsCorrespondants
            ];
        }
    }

    /**
     * Récupère tous les dons disponibles à la vente
     */
/**
 * Récupère tous les dons disponibles à la vente avec valeur estimée basée sur prix unitaire
 */
public function getDonsAVendre($idVille = null) {
    $sql = "SELECT 
                d.*,
                c.nom as categorie_nom,
                u.nom as unite_nom,
                u.symbole as unite_symbole,
                u.type as unite_type,
                v.nom as ville_attribuee,
                CASE 
                    WHEN d.prix_unitaire IS NOT NULL THEN d.prix_unitaire * d.quantite
                    ELSE d.quantite * 1000
                END as valeur_estimee,
                CASE 
                    WHEN d.prix_unitaire IS NOT NULL THEN CONCAT('Prix unitaire: ', FORMAT(d.prix_unitaire, 0), ' Ar')
                    ELSE 'Estimation par défaut'
                END as type_prix,
                d.prix_unitaire,
                (SELECT COUNT(*) FROM besoin b 
                 WHERE b.idville = d.idville_attribuee 
                 AND b.idcategorie = d.idcategorie 
                 AND b.id_unite = d.id_unite 
                 AND b.quantite > COALESCE(b.quantite_recue, 0)) as besoins_correspondants
            FROM dons d
            LEFT JOIN categorie c ON d.idcategorie = c.id
            LEFT JOIN unite u ON d.id_unite = u.id
            LEFT JOIN ville v ON d.idville_attribuee = v.id
            WHERE d.idbesoin IS NULL
            AND u.type != 'monnaie'
            AND d.id NOT IN (SELECT iddon FROM vente)
            ORDER BY d.id DESC";
    
    if ($idVille) {
        $sql .= " AND d.idville_attribuee = :idVille";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['idVille' => $idVille]);
    } else {
        $stmt = $this->db->query($sql);
    }
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Récupère un don spécifique avec sa valeur basée sur prix unitaire
 */
public function getDonById($idDon) {
    $sql = "SELECT 
                d.*,
                c.nom as categorie_nom,
                u.nom as unite_nom,
                u.symbole as unite_symbole,
                u.type as unite_type,
                v.nom as ville_attribuee,
                CASE 
                    WHEN d.prix_unitaire IS NOT NULL THEN d.prix_unitaire * d.quantite
                    ELSE d.quantite * 1000
                END as valeur_estimee,
                d.prix_unitaire
            FROM dons d
            LEFT JOIN categorie c ON d.idcategorie = c.id
            LEFT JOIN unite u ON d.id_unite = u.id
            LEFT JOIN ville v ON d.idville_attribuee = v.id
            WHERE d.id = ?";
    
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$idDon]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

    /**
     * Calcule le montant récupéré après vente (avec pourcentage)
     */
    public function calculerMontantRecupere($montantVente, $pourcentage) {
        $reduction = ($montantVente * $pourcentage) / 100;
        return $montantVente - $reduction;
    }

    /**
     * Effectue la vente d'un don - TRANSFORME le don en argent
     */
    public function vendreDon($iddon, $montantVente, $description = null) {
        try {
            $this->db->beginTransaction();

            // Vérifier d'abord si le don peut être vendu
            $verification = $this->peutVendre($iddon);
            if (!$verification['peut_vendre']) {
                throw new \Exception($verification['message']);
            }

            // Récupérer les informations du don
            $don = $this->getDonById($iddon);
            if (!$don) {
                throw new \Exception("Don non trouvé");
            }

            // Vérifier que le don n'est pas déjà vendu
            $sql = "SELECT id FROM vente WHERE iddon = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$iddon]);
            if ($stmt->fetch()) {
                throw new \Exception("Ce don a déjà été vendu");
            }

            // Récupérer le pourcentage de vente
            $paramModel = new \app\models\ParametresModel($this->db);
            $pourcentage = $paramModel->getValeur('pourcentage_vente', 10);

            // Calculer le montant récupéré
            $montantRecupere = $this->calculerMontantRecupere($montantVente, $pourcentage);

            // Récupérer l'unité "Ariary"
            $sql = "SELECT id FROM unite WHERE symbole = 'Ar' AND type = 'monnaie' LIMIT 1";
            $stmt = $this->db->query($sql);
            $uniteAr = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$uniteAr) {
                throw new \Exception("Unité Ariary non trouvée dans la base");
            }

            // Transformer le don existant en don d'argent
            $nouvelleDescription = "[VENDU] " . $don['description'];
            if (!empty($description)) {
                $nouvelleDescription .= " - " . $description;
            }
            
            $sql = "UPDATE dons SET 
                        idbesoin = NULL,
                        description = ?,
                        quantite = ?,
                        id_unite = ?,
                        montant_restant = ?,
                        valeur = NULL
                    WHERE id = ?";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $nouvelleDescription,
                $montantRecupere,
                $uniteAr['id'],
                $montantRecupere,
                $iddon
            ]);

            // Enregistrer la vente dans l'historique
            $sql = "INSERT INTO vente (iddon, montant_vente, montant_recupere, pourcentage_applique, description) 
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $iddon,
                $montantVente,
                $montantRecupere,
                $pourcentage,
                $description
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Don vendu avec succès. Montant récupéré: " . number_format($montantRecupere, 0, ',', ' ') . " Ar (après déduction de {$pourcentage}%)",
                'data' => [
                    'id_vente' => $this->db->lastInsertId(),
                    'montant_vente' => $montantVente,
                    'montant_recupere' => $montantRecupere,
                    'pourcentage' => $pourcentage,
                    'id_don_transformé' => $iddon
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'besoins' => isset($verification) ? $verification['besoins'] : []
            ];
        }
    }

    /**
     * Récupère l'historique des ventes
     */
    public function getStatsVentesParVille() {
        $sql = "SELECT 
                    COALESCE(ville.id, 0) as id,
                    COALESCE(ville.nom, 'Toutes') as ville_nom,
                    COUNT(v.id) as nombre_ventes,
                    COALESCE(SUM(v.montant_vente), 0) as total_ventes,
                    COALESCE(SUM(v.montant_recupere), 0) as total_recupere,
                    COALESCE(AVG(v.pourcentage_applique), 0) as pourcentage_moyen
                FROM ville
                LEFT JOIN dons d ON d.idville_attribuee = ville.id
                LEFT JOIN vente v ON v.iddon = d.id
                GROUP BY ville.id, ville.nom
                ORDER BY total_ventes DESC";
        
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as &$row) {
            $row['nombre_ventes'] = (int)($row['nombre_ventes'] ?? 0);
            $row['total_ventes'] = (float)($row['total_ventes'] ?? 0);
            $row['total_recupere'] = (float)($row['total_recupere'] ?? 0);
            $row['pourcentage_moyen'] = (float)($row['pourcentage_moyen'] ?? 0);
        }
        
        return $results;
    }

    /**
     * Récupère l'historique des ventes
     */
    public function getHistoriqueVentes($idVille = null) {
        $sql = "SELECT 
                    v.*,
                    d.description as don_description,
                    d.quantite as don_quantite,
                    u.symbole as don_unite,
                    cat.nom as don_categorie,
                    ville.nom as ville_attribuee
                FROM vente v
                JOIN dons d ON v.iddon = d.id
                LEFT JOIN unite u ON d.id_unite = u.id
                LEFT JOIN categorie cat ON d.idcategorie = cat.id
                LEFT JOIN ville ville ON d.idville_attribuee = ville.id
                ORDER BY v.date_vente DESC";
        
        if ($idVille) {
            $sql = "SELECT 
                        v.*,
                        d.description as don_description,
                        d.quantite as don_quantite,
                        u.symbole as don_unite,
                        cat.nom as don_categorie,
                        ville.nom as ville_attribuee
                    FROM vente v
                    JOIN dons d ON v.iddon = d.id
                    LEFT JOIN unite u ON d.id_unite = u.id
                    LEFT JOIN categorie cat ON d.idcategorie = cat.id
                    LEFT JOIN ville ville ON d.idville_attribuee = ville.id
                    WHERE d.idville_attribuee = :idVille
                    ORDER BY v.date_vente DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idVille' => $idVille]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($results as &$row) {
            $row['montant_vente'] = (float)($row['montant_vente'] ?? 0);
            $row['montant_recupere'] = (float)($row['montant_recupere'] ?? 0);
            $row['pourcentage_applique'] = (float)($row['pourcentage_applique'] ?? 0);
        }
        
        return $results;
    }

    /**
     * Met à jour la valeur d'un don
     */
    public function mettreAJourValeur($iddon, $valeur) {
        try {
            $sql = "UPDATE dons SET valeur = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $result = $stmt->execute([$valeur, $iddon]);
            
            return [
                'success' => $result,
                'message' => $result ? "Valeur mise à jour avec succès" : "Erreur lors de la mise à jour"
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}