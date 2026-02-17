<?php

namespace App\Models;

use PDO;

class VilleModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function all()
    {
        $sql = "SELECT * FROM ville";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find($idVille)
    {
        $sql = "SELECT * FROM ville WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idVille]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getDepenseville($idVille)
    {
        $sql = "SELECT SUM(montant) AS total FROM achat WHERE idville = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$idVille]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row['total'] ?? 0;
    }
}
