<?php

namespace App\Models;

use App\Config\Database;
use PDO;

class AffectationModel
{
    public static function insert($data)
    {
        $db = Database::connect();
        $sql = "INSERT INTO affectations (iddon, idbesoin, quantite) VALUES (?, ?, ?)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            $data['iddon'],
            $data['idbesoin'],
            $data['quantite']
        ]);
        return $db->lastInsertId();
    }

    public static function byBesoin($idBesoin)
    {
        $db = Database::connect();
        $sql = "SELECT a.*, d.description as don_description, p.nom as produit_nom 
                FROM affectations a
                JOIN dons d ON a.iddon = d.id
                LEFT JOIN produits p ON d.idproduit = p.id
                WHERE a.idbesoin = ?";
        $stmt = $db->prepare($sql);
        $stmt->execute([$idBesoin]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public static function totalByBesoin($idBesoin)
    {
         $db = Database::connect();
         $stmt = $db->prepare("SELECT SUM(quantite) as total FROM affectations WHERE idbesoin = ?");
         $stmt->execute([$idBesoin]);
         $res = $stmt->fetch(PDO::FETCH_ASSOC);
         return $res['total'] ?? 0;
    }
}
