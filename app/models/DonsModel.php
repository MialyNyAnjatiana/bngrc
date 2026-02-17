<?php

namespace app\models;

use PDO;

class DonsModel
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function afficherDonsParVille($idVille)
    {
        $sql = "SELECT 
                d.id,
                d.description,
                d.quantite,
                u.nom AS unite_nom,
                u.symbole AS unite_symbole,
                u.type AS unite_type,
                v.nom AS nom_ville
            FROM dons d
            LEFT JOIN besoin b ON d.idbesoin = b.id
            LEFT JOIN ville v ON COALESCE(b.idville, d.idville_attribuee) = v.id
            JOIN unite u ON d.id_unite = u.id
            WHERE v.id = ?";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idVille]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /**
     * Ajoute un don sans besoin avec catégorie et unité
     */
   /**
 * Ajoute un don sans besoin avec catégorie, unité et prix unitaire
 */
public function ajouterDonSansBesoin($description, $quantite, $idUnite, $idVille, $idCategorie = null, $prixUnitaire = null)
{
    try {
        $this->db->beginTransaction();

        // Vérifier si l'unité est de type monnaie
        $stmtUnite = $this->db->prepare("SELECT type, symbole FROM unite WHERE id = ?");
        $stmtUnite->execute([$idUnite]);
        $unite = $stmtUnite->fetch(PDO::FETCH_ASSOC);

        $estMonnaie = ($unite && $unite['type'] === 'monnaie');

        // Si c'est un don en argent, initialiser montant_restant
        if ($estMonnaie) {
            $sql = "INSERT INTO dons (idbesoin, idcategorie, description, quantite, prix_unitaire, id_unite, montant_restant, idville_attribuee) 
                    VALUES (NULL, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idCategorie, $description, $quantite, $prixUnitaire, $idUnite, $quantite, $idVille]);
        } else {
            $sql = "INSERT INTO dons (idbesoin, idcategorie, description, quantite, prix_unitaire, id_unite, idville_attribuee) 
                    VALUES (NULL, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idCategorie, $description, $quantite, $prixUnitaire, $idUnite, $idVille]);
        }

        $idDon = $this->db->lastInsertId();

        $this->db->commit();
        return $idDon;
    } catch (\Exception $e) {
        $this->db->rollBack();
        error_log("Erreur ajout don: " . $e->getMessage());
        return false;
    }
}

    /**
     * Récupère tous les dons non liés
     */
    public function getDonsNonLies()
    {
        $sql = "SELECT 
                    d.*,
                    c.nom as categorie_nom,
                    u.nom as unite_nom,
                    u.symbole as unite_symbole,
                    u.type as unite_type,
                    v.nom as ville_attribuee,
                    CASE 
                        WHEN u.type = 'monnaie' THEN '💰 Don en argent'
                        ELSE '📦 Don en nature'
                    END as type_don,
                    CASE 
                        WHEN u.type = 'monnaie' THEN COALESCE(d.montant_restant, d.quantite)
                        ELSE d.quantite
                    END as quantite_disponible
                FROM dons d
                LEFT JOIN categorie c ON d.idcategorie = c.id
                LEFT JOIN unite u ON d.id_unite = u.id
                LEFT JOIN ville v ON d.idville_attribuee = v.id
                WHERE d.idbesoin IS NULL
                AND (u.type != 'monnaie' OR (d.montant_restant IS NULL OR d.montant_restant > 0))
                ORDER BY d.id DESC";

        $stmt = $this->db->query($sql);
        $dons = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Formater l'affichage pour les dons en argent
        foreach ($dons as &$don) {
            if ($don['unite_type'] === 'monnaie') {
                $don['montant_affiche'] = number_format($don['quantite_disponible'], 0, ',', ' ') . ' ' . $don['unite_symbole'];
            }
        }

        return $dons;
    }

    /**
     * Récupère les dons non liés pour une ville spécifique
     */
    public function getDonsNonLiesParVille($idVille)
    {
        $sql = "SELECT 
                    d.*,
                    c.nom as categorie_nom,
                    u.nom as unite_nom,
                    u.symbole as unite_symbole,
                    u.type as unite_type,
                    CASE 
                        WHEN u.type = 'monnaie' THEN COALESCE(d.montant_restant, d.quantite)
                        ELSE d.quantite
                    END as quantite_disponible
                FROM dons d
                LEFT JOIN categorie c ON d.idcategorie = c.id
                LEFT JOIN unite u ON d.id_unite = u.id
                WHERE d.idbesoin IS NULL
                AND d.idville_attribuee = ?
                AND (u.type != 'monnaie' OR (d.montant_restant IS NULL OR d.montant_restant > 0))
                ORDER BY d.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idVille]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les dons en argent disponibles
     */
    public function getDonsArgentDisponibles($idVille = null)
    {
        $sql = "SELECT 
                    d.*,
                    v.nom as ville_attribuee,
                    u.symbole as unite_symbole,
                    COALESCE(d.montant_restant, d.quantite) as montant_disponible
                FROM dons d
                JOIN unite u ON d.id_unite = u.id
                LEFT JOIN ville v ON d.idville_attribuee = v.id
                WHERE u.type = 'monnaie'
                AND d.idbesoin IS NULL
                AND (d.montant_restant IS NULL OR d.montant_restant > 0)";

        if ($idVille) {
            $sql .= " AND d.idville_attribuee = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idVille]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Vérifie si un don peut être lié à un besoin
     */
    public function verifierCompatibiliteDonBesoin($idDon, $idBesoin)
    {
        // Récupérer les informations du don
        $sqlDon = "SELECT d.*, u.type as unite_type, u.symbole as unite_symbole, u.id as unite_id, v.nom as ville_attribuee
                  FROM dons d
                  JOIN unite u ON d.id_unite = u.id
                  LEFT JOIN ville v ON d.idville_attribuee = v.id
                  WHERE d.id = ?";
        $stmtDon = $this->db->prepare($sqlDon);
        $stmtDon->execute([$idDon]);
        $don = $stmtDon->fetch(PDO::FETCH_ASSOC);

        if (!$don) {
            return [
                'success' => false,
                'message' => 'Don non trouvé',
                'data' => null
            ];
        }

        // Récupérer les informations du besoin
        $sqlBesoin = "SELECT b.*, v.nom as ville_nom, v.id as ville_id,
                             u.type as unite_type, u.symbole as unite_symbole, u.id as unite_id
                     FROM besoin b
                     JOIN ville v ON b.idville = v.id
                     JOIN unite u ON b.id_unite = u.id
                     WHERE b.id = ?";
        $stmtBesoin = $this->db->prepare($sqlBesoin);
        $stmtBesoin->execute([$idBesoin]);
        $besoin = $stmtBesoin->fetch(PDO::FETCH_ASSOC);

        if (!$besoin) {
            return [
                'success' => false,
                'message' => 'Besoin non trouvé',
                'data' => null
            ];
        }

        // Vérification 1: Le don doit être attribué à la même ville que le besoin
        if ($don['idville_attribuee'] != $besoin['ville_id']) {
            return [
                'success' => false,
                'message' => "Ce don est attribué à {$don['ville_attribuee']} mais le besoin est à {$besoin['ville_nom']}. Les dons ne peuvent être utilisés que dans leur ville d'attribution.",
                'data' => ['don' => $don, 'besoin' => $besoin]
            ];
        }

        // Vérification 2: Même unité de mesure (particulièrement important pour l'argent)
        if ($don['id_unite'] != $besoin['id_unite']) {
            return [
                'success' => false,
                'message' => "Les unités de mesure ne correspondent pas: {$don['unite_symbole']} vs {$besoin['unite_symbole']}. Les dons et besoins doivent avoir la même unité.",
                'data' => ['don' => $don, 'besoin' => $besoin]
            ];
        }

        // CAS SPÉCIAL: Don en argent pour besoin financier
        if ($don['unite_type'] === 'monnaie' && $besoin['unite_type'] === 'monnaie') {
            $quantiteRestante = $besoin['quantite'] - ($besoin['quantite_recue'] ?? 0);
            $montantDisponible = $don['montant_restant'] ?? $don['quantite'];

            if ($montantDisponible <= 0) {
                return [
                    'success' => false,
                    'message' => "Ce don en argent n'a plus de fonds disponibles",
                    'data' => ['don' => $don, 'besoin' => $besoin]
                ];
            }

            // Vérification que le montant disponible ne dépasse pas le besoin restant
            if ($montantDisponible > $quantiteRestante) {
                return [
                    'success' => false,
                    'message' => "Le montant disponible ({$montantDisponible} {$don['unite_symbole']}) dépasse le besoin restant ({$quantiteRestante} {$besoin['unite_symbole']}). Vous pouvez lier partiellement en utilisant le système d'achat.",
                    'data' => [
                        'don' => $don,
                        'besoin' => $besoin,
                        'quantite_restante' => $quantiteRestante,
                        'montant_disponible' => $montantDisponible,
                        'est_achat' => false,
                        'liaison_partielle_possible' => false
                    ]
                ];
            }

            return [
                'success' => true,
                'message' => "Don en argent compatible avec le besoin financier à {$besoin['ville_nom']}",
                'data' => [
                    'don' => $don,
                    'besoin' => $besoin,
                    'quantite_restante' => $quantiteRestante,
                    'montant_disponible' => $montantDisponible,
                    'est_achat' => false
                ]
            ];
        }

        // CAS: Don en argent pour besoin non financier (rediriger vers achat)
        if ($don['unite_type'] === 'monnaie' && $besoin['unite_type'] !== 'monnaie') {
            return [
                'success' => false,
                'message' => "Les dons en argent doivent être utilisés via la page d'achat pour acheter des biens à {$besoin['ville_nom']}",
                'data' => [
                    'don' => $don,
                    'besoin' => $besoin,
                    'redirection' => '/achat'
                ]
            ];
        }

        // CAS: Don non financier pour besoin financier
        if ($don['unite_type'] !== 'monnaie' && $besoin['unite_type'] === 'monnaie') {
            return [
                'success' => false,
                'message' => "Un don en nature ne peut pas être lié à un besoin financier. Seuls les dons en argent peuvent être liés à des besoins financiers.",
                'data' => ['don' => $don, 'besoin' => $besoin]
            ];
        }

        // CAS NORMAL: Don en nature/matériaux
        // Vérification 3: Même catégorie
        if ($don['idcategorie'] != $besoin['idcategorie']) {
            return [
                'success' => false,
                'message' => "Les catégories ne correspondent pas",
                'data' => ['don' => $don, 'besoin' => $besoin]
            ];
        }

        // Vérification 4: Quantité du don <= Quantité restante du besoin
        $quantiteRestante = $besoin['quantite'] - ($besoin['quantite_recue'] ?? 0);

        if ($don['quantite'] > $quantiteRestante) {
            return [
                'success' => false,
                'message' => "La quantité du don ({$don['quantite']} {$don['unite_symbole']}) dépasse la quantité restante du besoin ({$quantiteRestante} {$besoin['unite_symbole']})",
                'data' => [
                    'don' => $don,
                    'besoin' => $besoin,
                    'quantite_restante' => $quantiteRestante
                ]
            ];
        }

        // Toutes les vérifications sont passées
        return [
            'success' => true,
            'message' => "Le don peut être lié au besoin à {$besoin['ville_nom']}",
            'data' => [
                'don' => $don,
                'besoin' => $besoin,
                'quantite_restante' => $quantiteRestante,
                'est_achat' => false
            ]
        ];
    }

    /**
     * Lie un don à un besoin après vérification
     */
    public function lierDonABesoin($idDon, $idBesoin)
    {
        try {
            $this->db->beginTransaction();

            // Vérifier d'abord la compatibilité
            $verification = $this->verifierCompatibiliteDonBesoin($idDon, $idBesoin);

            if (!$verification['success']) {
                return [
                    'success' => false,
                    'message' => $verification['message']
                ];
            }

            $don = $verification['data']['don'];
            $besoin = $verification['data']['besoin'];
            $quantiteRestante = $verification['data']['quantite_restante'];

            // CAS: Don en argent pour besoin financier
            if ($don['unite_type'] === 'monnaie' && $besoin['unite_type'] === 'monnaie') {
                $montantUtilise = $don['montant_restant'] ?? $don['quantite'];

                // Mettre à jour le don
                $nouveauMontant = 0; // Le don est entièrement utilisé

                // Lier le don au besoin
                $sqlUpdateDon = "UPDATE dons SET idbesoin = ?, montant_restant = 0 WHERE id = ?";
                $stmtUpdate = $this->db->prepare($sqlUpdateDon);
                $stmtUpdate->execute([$idBesoin, $idDon]);

                // Mettre à jour le besoin avec la quantité reçue
                $nouvelleQuantiteRecue = ($besoin['quantite_recue'] ?? 0) + $montantUtilise;
                $sqlUpdateBesoin = "UPDATE besoin SET quantite_recue = ? WHERE id = ?";
                $stmtUpdateBesoin = $this->db->prepare($sqlUpdateBesoin);
                $stmtUpdateBesoin->execute([$nouvelleQuantiteRecue, $idBesoin]);

                $this->db->commit();

                return [
                    'success' => true,
                    'message' => "Don en argent de " . number_format($montantUtilise, 0, ',', ' ') . " {$don['unite_symbole']} lié au besoin financier à {$besoin['ville_nom']}. Besoin totalement satisfait."
                ];
            }

            // CAS NORMAL: Don en nature/matériaux
            // Mettre à jour le don avec l'ID du besoin
            $sqlUpdateDon = "UPDATE dons SET idbesoin = ? WHERE id = ?";
            $stmtUpdate = $this->db->prepare($sqlUpdateDon);
            $stmtUpdate->execute([$idBesoin, $idDon]);

            // Mettre à jour la quantité reçue du besoin
            $nouvelleQuantiteRecue = ($besoin['quantite_recue'] ?? 0) + $don['quantite'];

            $sqlUpdateBesoin = "UPDATE besoin SET quantite_recue = ? WHERE id = ?";
            $stmtUpdateBesoin = $this->db->prepare($sqlUpdateBesoin);
            $stmtUpdateBesoin->execute([$nouvelleQuantiteRecue, $idBesoin]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => "Le don a été lié avec succès au besoin à {$besoin['ville_nom']}. Quantité restante: " . ($quantiteRestante - $don['quantite']) . " {$don['unite_symbole']}"
            ];
        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Erreur lors de la liaison: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupère tous les besoins non satisfaits pour une ville
     */
    public function getBesoinsNonSatisfaits($idVille)
    {
        $sql = "SELECT 
                    b.*,
                    v.nom as ville_nom,
                    c.nom as categorie_nom,
                    u.nom as unite_nom,
                    u.symbole as unite_symbole,
                    u.type as unite_type,
                    (b.quantite - COALESCE(b.quantite_recue, 0)) as quantite_restante,
                    CASE 
                        WHEN u.type = 'monnaie' THEN '💰 Besoin financier'
                        ELSE '📋 Besoin en nature'
                    END as type_besoin
                FROM besoin b
                JOIN ville v ON b.idville = v.id
                JOIN categorie c ON b.idcategorie = c.id
                JOIN unite u ON b.id_unite = u.id
                WHERE b.idville = ? 
                AND b.quantite > COALESCE(b.quantite_recue, 0)
                ORDER BY b.date_creation DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idVille]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère tous les besoins financiers non satisfaits
     */
    public function getBesoinsFinanciersNonSatisfaits($idVille = null)
    {
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
                WHERE u.type = 'monnaie'
                AND b.quantite > COALESCE(b.quantite_recue, 0)";

        if ($idVille) {
            $sql .= " AND b.idville = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$idVille]);
        } else {
            $stmt = $this->db->query($sql);
        }

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les catégories
     */
    public function getCategories()
    {
        $sql = "SELECT * FROM categorie ORDER BY nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère toutes les unités
     */
    public function getUnites()
    {
        $sql = "SELECT * FROM unite ORDER BY type, nom";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Récupère les unités par type
     */
    public function getUnitesByType($type)
    {
        $sql = "SELECT * FROM unite WHERE type = ? ORDER BY nom";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$type]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
