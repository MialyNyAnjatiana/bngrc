<?php

namespace app\controllers;

use Flight;
use app\Config\Database;
use PDO;

class RecapController
{
    public static function index()
    {
        Flight::render('recap');
    }

    public function data()
    {
        $db = Flight::db();

        $global = [
            'besoins_totaux' => (float)$this->getTotalBesoins($db),
            'besoins_satisfaits' => (float)$this->getBesoinsSatisfaits($db),
            'dons_recus' => (float)$this->getDonsRecus($db),
            'dons_dispatches' => (float)$this->getDonsDispatches($db)
        ];

        $categoriesData = [];
        $categories = $db->query("SELECT * FROM categorie")->fetchAll(PDO::FETCH_ASSOC);

        foreach ($categories as $cat) {
            $categoriesData[] = [
                'id' => $cat['id'],
                'nom' => $cat['nom'],
                'besoins_totaux' => (float)$this->getTotalBesoins($db, $cat['id']),
                'besoins_satisfaits' => (float)$this->getBesoinsSatisfaits($db, $cat['id']),
                'dons_recus' => (float)$this->getDonsRecus($db, $cat['id']),
                'dons_dispatches' => (float)$this->getDonsDispatches($db, $cat['id'])
            ];
        }

        Flight::json(array_merge($global, ['categories' => $categoriesData]));
    }

    private function getTotalBesoins($db, $categoryId = null)
    {
        $sql = "SELECT SUM(quantite * prix_unitaire) as total
            FROM besoin";

        if ($categoryId) {
            $sql .= " WHERE idcategorie = " . (int)$categoryId;
        }

        $result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    private function getBesoinsSatisfaits($db, $categoryId = null)
    {
        // 1. Via Achats directs (quand on achète pour un besoin)
        $sqlAchats = "SELECT SUM(a.montant) as total 
                  FROM achat a
                  JOIN besoin b ON a.idbesoin = b.id";

        if ($categoryId) {
            $sqlAchats .= " WHERE b.idcategorie = " . (int)$categoryId;
        }

        $totalAchats = $db->query($sqlAchats)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // 2. Via dons liés directement à des besoins (dons affectés)
        $sqlDonsLies = "SELECT SUM(b.quantite_recue * b.prix_unitaire) as total
                    FROM besoin b
                    WHERE b.quantite_recue > 0";

        if ($categoryId) {
            $sqlDonsLies .= " AND b.idcategorie = " . (int)$categoryId;
        }

        $totalDonsLies = $db->query($sqlDonsLies)->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        // On prend le plus grand des deux pour éviter les doublons
        // (un besoin peut être satisfait soit par achat, soit par don direct)
        return max($totalAchats, $totalDonsLies);
    }

    private function getDonsRecus($db, $categoryId = null)
    {
        $sql = "SELECT SUM(
                CASE 
                    WHEN u.nom = 'ariary' THEN d.quantite 
                    ELSE d.quantite * b.prix_unitaire 
                END
            ) as total
            FROM dons d
            LEFT JOIN besoin b ON d.idbesoin = b.id
            LEFT JOIN unite u ON d.id_unite = u.id";

        if ($categoryId) {
            $sql .= " WHERE d.idcategorie = " . (int)$categoryId;
        }

        $result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    private function getDonsDispatches($db, $categoryId = null)
    {
        // Somme des montants des achats + valeur des dons liés
        $sql = "SELECT 
                (SELECT COALESCE(SUM(montant), 0) FROM achat a
                 JOIN besoin b ON a.idbesoin = b.id" .
            ($categoryId ? " WHERE b.idcategorie = " . (int)$categoryId : "") . "
                ) +
                (SELECT COALESCE(SUM(b.quantite_recue * b.prix_unitaire), 0) FROM besoin b
                 WHERE b.quantite_recue > 0" .
            ($categoryId ? " AND b.idcategorie = " . (int)$categoryId : "") . "
                ) as total";

        $result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Optional: Helper method to get unite type
    private function isMonetaryUnit($db, $id_unite)
    {
        $sql = "SELECT nom FROM unite WHERE id = " . (int)$id_unite;
        $result = $db->query($sql)->fetch(PDO::FETCH_ASSOC);
        return ($result && $result['nom'] === 'ariary');
    }
}
