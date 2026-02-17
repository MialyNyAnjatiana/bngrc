<?php

namespace app\controllers;

use Flight;
use app\models\AchatModel;
use app\models\VilleModel;
use app\models\BesoinModel;
use app\models\DonsModel;

class VilleController
{
    public static function home()
    {
        $db = Flight::db();

        $villeModel  = new VilleModel($db);
        $besoinModel = new BesoinModel($db);
        $donsModel   = new DonsModel($db);
        $achatModel  = new AchatModel($db);

        $villes = $villeModel->all();
        $achats = $achatModel->getTotalAchatsParVille();

        // Re-index achats by ville id
        $achatsByVille = [];
        foreach ($achats as $row) {
            $achatsByVille[$row['id']] = $row;
        }

        $result = [];
        foreach ($villes as $ville) {
            $idVille = $ville['id'];

            $result[$idVille] = [
                'ville'    => $ville,
                'besoins'  => array_filter($besoinModel->all(), fn($b) => $b['idville'] == $idVille),
                'dons'     => $donsModel->afficherDonsParVille($idVille),
                'depenses' => $achatsByVille[$idVille]['total_montant'] ?? 0,
                'achats'   => $achatsByVille[$idVille]['nombre_achats'] ?? 0,
                'quantite' => $achatsByVille[$idVille]['total_quantite'] ?? 0
            ];
        }

        Flight::render('home', ['villes' => $result]);
    }
}
