<?php

namespace app\controllers;

use Flight;
use app\utils\DataManager;

class DataController
{
    private $db;

    public function __construct()
    {
        $this->db = Flight::db();
    }

    public function reset()
    {
        try {
            $this->db->beginTransaction();

            // Disable foreign key checks temporarily
            $this->db->exec("SET FOREIGN_KEY_CHECKS = 0");

            // Clear runtime tables
            $this->db->exec("DELETE FROM achat WHERE is_default = 1");
            $this->db->exec("DELETE FROM dons WHERE is_default = 1");
            $this->db->exec("DELETE FROM besoin WHERE is_default = 1");
            $this->db->exec("DELETE FROM vente WHERE is_default = 1");

            $this->db->exec("SET FOREIGN_KEY_CHECKS = 1");
            $this->db->commit();

            // Redirect immediately after success
            Flight::redirect('/');
        } catch (\Exception $e) {
            $this->db->rollBack();
            error_log("Erreur reset: " . $e->getMessage());

            // Redirect with error message
            Flight::redirect('/?error=' . urlencode($e->getMessage()));
        }
    }
}
