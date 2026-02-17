<?php

namespace app\models;

use PDO;

class BesoinModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all()
    {
        return $this->db
            ->query("SELECT * FROM besoin")
            ->fetchAll(PDO::FETCH_ASSOC);
    }

    // CORRECTION: Ajout du paramètre prixUnitaire et ordre correct
    public function insert($idVille, $idCategorie, $description, $quantite, $idUnite, $prixUnitaire = null)
    {
        try {
            $this->db->beginTransaction();

            // Vérifier si l'unité existe
            $stmtUnite = $this->db->prepare("SELECT id, type, symbole FROM unite WHERE id = ?");
            $stmtUnite->execute([$idUnite]);
            $unite = $stmtUnite->fetch(PDO::FETCH_ASSOC);

            if (!$unite) {
                throw new \Exception("Unité introuvable");
            }

            // Vérifier si la ville existe
            $stmtVille = $this->db->prepare("SELECT id FROM ville WHERE id = ?");
            $stmtVille->execute([$idVille]);
            if (!$stmtVille->fetch()) {
                throw new \Exception("Ville introuvable");
            }

            // Préparer l'insertion du besoin avec le prix unitaire
            $sql = "INSERT INTO besoin (idville, idcategorie, description, quantite, id_unite, prix_unitaire, ordre, date_besoin) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $idVille,
                $idCategorie ?: null,
                $description,
                $quantite,
                $idUnite,
                $prixUnitaire,
                0 // ordre par défaut
            ]);

            $idBesoin = $this->db->lastInsertId();

            $this->db->commit();
            return $idBesoin;
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur ajout besoin: " . $e->getMessage());
            return false;
        }
    }

    public function getPrixUnitaire($id)
    {
        $sql = "SELECT prix_unitaire FROM categorie WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? (float)$result['prix_unitaire'] : null;
    }

    public function updatePrixUnitaire($id, $prix)
    {
        $sql = "UPDATE categorie SET prix_unitaire = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$prix, $id]);
    }
}