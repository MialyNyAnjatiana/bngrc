<?php
// app/models/AchatModel.php

namespace app\models;

use PDO;

class AchatModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère les dons en argent disponibles pour une ville spécifique
     */
    public function getDonsArgentDisponibles($idVille = null) {
        $sql = "SELECT 
                    d.*,
                    v.nom as ville_attribuee,
                    c.nom as categorie_nom,
                    u.nom as unite_nom,
                    u.symbole as unite_symbole,
                    COALESCE(d.montant_restant, d.quantite) as montant_disponible
                FROM dons d
                JOIN unite u ON d.id_unite = u.id
                LEFT JOIN ville v ON d.idville_attribuee = v.id
                LEFT JOIN categorie c ON d.idcategorie = c.id
                WHERE u.type = 'monnaie'
                AND d.idbesoin IS NULL
                AND (d.montant_restant IS NULL OR d.montant_restant > 0)";
        
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
     * Récupère les besoins achetables pour une ville spécifique
     */
    public function getBesoinsAchetables($idVille = null) {
        $sql = "SELECT 
                    b.*,
                    v.nom as ville_nom,
                    c.nom as categorie_nom,
                    u.nom as unite_nom,
                    u.symbole as unite_symbole,
                    u.type as unite_type,
                    (b.quantite - COALESCE(b.quantite_recue, 0)) as quantite_restante
                FROM besoin b
                JOIN ville v ON b.idville = v.id
                JOIN categorie c ON b.idcategorie = c.id
                JOIN unite u ON b.id_unite = u.id
                WHERE b.prix_unitaire IS NOT NULL 
                AND u.type != 'monnaie'
                AND b.quantite > COALESCE(b.quantite_recue, 0)";
        
        if ($idVille) {
            $sql .= " AND v.id = :idVille";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idVille' => $idVille]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Effectue un achat avec vérification de la ville d'attribution
     */
    public function effectuerAchat($iddon, $idbesoin, $quantite) {
        try {
            $this->db->beginTransaction();

            // Récupérer le don avec sa ville d'attribution
            $stmt = $this->db->prepare("
                SELECT d.*, u.type as unite_type, u.symbole as unite_symbole, 
                       v.nom as ville_attribuee, v.id as ville_attribuee_id
                FROM dons d
                JOIN unite u ON d.id_unite = u.id
                LEFT JOIN ville v ON d.idville_attribuee = v.id
                WHERE d.id = ?
            ");
            $stmt->execute([$iddon]);
            $don = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$don || $don['unite_type'] != 'monnaie') {
                throw new \Exception("Don non valide ou n'est pas en argent");
            }

            // Vérifier que le don est attribué à une ville
            if (!$don['ville_attribuee_id']) {
                throw new \Exception("Ce don n'est attribué à aucune ville. Veuillez d'abord l'attribuer à une ville.");
            }

            // Vérifier le montant disponible
            $montantDisponible = $don['montant_restant'] ?? $don['quantite'];
            if ($montantDisponible <= 0) {
                throw new \Exception("Ce don n'a plus d'argent disponible");
            }

            // Récupérer le besoin avec sa ville
            $stmt = $this->db->prepare("
                SELECT b.*, v.nom as ville_nom, v.id as ville_id,
                       u.symbole as unite_symbole, u.nom as unite_nom,
                       c.nom as categorie_nom
                FROM besoin b
                JOIN ville v ON b.idville = v.id
                JOIN unite u ON b.id_unite = u.id
                JOIN categorie c ON b.idcategorie = c.id
                WHERE b.id = ?
            ");
            $stmt->execute([$idbesoin]);
            $besoin = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$besoin || !$besoin['prix_unitaire']) {
                throw new \Exception("Besoin non valide ou sans prix unitaire");
            }

            // VÉRIFICATION CRITIQUE: La ville du besoin doit correspondre à la ville d'attribution du don
            if ($besoin['ville_id'] != $don['ville_attribuee_id']) {
                throw new \Exception(
                    "Ce don est attribué à {$don['ville_attribuee']} mais vous essayez d'acheter pour {$besoin['ville_nom']}. " .
                    "Les dons ne peuvent être utilisés que dans leur ville d'attribution."
                );
            }

            // Vérifier la quantité restante
            $quantiteRestante = $besoin['quantite'] - ($besoin['quantite_recue'] ?? 0);
            if ($quantite > $quantiteRestante) {
                throw new \Exception("Quantité demandée ($quantite) dépasse le restant ($quantiteRestante)");
            }

            // Calculer le montant
            $montant = $quantite * $besoin['prix_unitaire'];
            if ($montant > $montantDisponible) {
                throw new \Exception("Montant insuffisant. Disponible: " . number_format($montantDisponible, 0, ',', ' ') . " Ar, Nécessaire: " . number_format($montant, 0, ',', ' ') . " Ar");
            }

            // Créer l'achat
            $stmt = $this->db->prepare("
                INSERT INTO achat (iddon, idbesoin, quantite, montant) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$iddon, $idbesoin, $quantite, $montant]);

            // Mettre à jour le montant restant du don
            $nouveauMontant = $montantDisponible - $montant;
            $stmt = $this->db->prepare("UPDATE dons SET montant_restant = ? WHERE id = ?");
            $stmt->execute([$nouveauMontant, $iddon]);

            // Mettre à jour la quantité reçue du besoin
            $nouvelleQuantiteRecue = ($besoin['quantite_recue'] ?? 0) + $quantite;
            $stmt = $this->db->prepare("UPDATE besoin SET quantite_recue = ? WHERE id = ?");
            $stmt->execute([$nouvelleQuantiteRecue, $idbesoin]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Achat effectué avec succès à {$besoin['ville_nom']}. Montant: " . number_format($montant, 0, ',', ' ') . " Ar",
                'data' => [
                    'montant' => $montant,
                    'quantite' => $quantite,
                    'ville' => $besoin['ville_nom'],
                    'restant_don' => $nouveauMontant,
                    'restant_besoin' => $quantiteRestante - $quantite
                ]
            ];

        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Récupère l'historique des achats avec filtrage par ville
     */
    public function getAchats($idVille = null) {
        $sql = "SELECT 
                    a.*,
                    d.description as don_description,
                    v_att.nom as don_ville,
                    b.description as besoin_description,
                    u.symbole as besoin_unite,
                    v.nom as ville_nom,
                    v.id as ville_id
                FROM achat a
                JOIN dons d ON a.iddon = d.id
                LEFT JOIN ville v_att ON d.idville_attribuee = v_att.id
                JOIN besoin b ON a.idbesoin = b.id
                JOIN unite u ON b.id_unite = u.id
                JOIN ville v ON b.idville = v.id
                ORDER BY a.date_achat DESC";
        
        if ($idVille) {
            $sql = "SELECT 
                        a.*,
                        d.description as don_description,
                        v_att.nom as don_ville,
                        b.description as besoin_description,
                        u.symbole as besoin_unite,
                        v.nom as ville_nom,
                        v.id as ville_id
                    FROM achat a
                    JOIN dons d ON a.iddon = d.id
                    LEFT JOIN ville v_att ON d.idville_attribuee = v_att.id
                    JOIN besoin b ON a.idbesoin = b.id
                    JOIN unite u ON b.id_unite = u.id
                    JOIN ville v ON b.idville = v.id
                    WHERE v.id = :idVille
                    ORDER BY a.date_achat DESC";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['idVille' => $idVille]);
        } else {
            $stmt = $this->db->query($sql);
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Calcule le montant total des achats par ville
     */
    public function getTotalAchatsParVille() {
        $sql = "SELECT 
                    v.id,
                    v.nom as ville_nom,
                    COUNT(a.id) as nombre_achats,
                    COALESCE(SUM(a.montant), 0) as total_montant,
                    COALESCE(SUM(a.quantite), 0) as total_quantite
                FROM achat a
                JOIN besoin b ON a.idbesoin = b.id
                JOIN ville v ON b.idville = v.id
                GROUP BY v.id, v.nom
                ORDER BY total_montant DESC";
        
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}