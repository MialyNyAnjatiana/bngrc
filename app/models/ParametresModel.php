<?php
// app/models/ParametresModel.php

namespace app\models;

use PDO;

class ParametresModel {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Récupère la valeur d'un paramètre
     */
    public function getValeur($cle, $default = null) {
        $sql = "SELECT valeur FROM parametres WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['valeur'] : $default;
    }

    /**
     * Met à jour la valeur d'un paramètre
     */
    public function setValeur($cle, $valeur, $description = null) {
        // Vérifier si la clé existe déjà
        $sql = "SELECT id FROM parametres WHERE cle = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$cle]);
        
        if ($stmt->fetch()) {
            // Mise à jour
            $sql = "UPDATE parametres SET valeur = ?, description = COALESCE(?, description) WHERE cle = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$valeur, $description, $cle]);
        } else {
            // Insertion
            $sql = "INSERT INTO parametres (cle, valeur, description) VALUES (?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$cle, $valeur, $description]);
        }
    }

    /**
     * Récupère tous les paramètres
     */
    public function getAll() {
        $sql = "SELECT * FROM parametres ORDER BY cle";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}