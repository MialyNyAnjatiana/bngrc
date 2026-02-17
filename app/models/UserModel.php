<?php

namespace app\models;

use PDO;

class UserModel
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function autoLogin(array $input): array
    {

        $errors = [];
        $values = [
            'email' => trim($input['email'] ?? ''),
            'nom'   => trim($input['nom'] ?? '')
        ];

        $mdp = $input['mdp'] ?? '';

        if ($values['email'] === '') {
            $errors['email'] = "Email obligatoire.";
        }

        if ($values['nom'] === '') {
            $errors['nom'] = "Nom obligatoire.";
        }

        if ($mdp === '') {
            $errors['mdp'] = "Mot de passe obligatoire.";
        }

        if (!empty($errors)) {
            return [
                'ok' => false,
                'errors' => $errors,
                'values' => $values
            ];
        }

        $user = $this->findByEmail($values['email']);

        if (!$user) {

            $hash = password_hash($mdp, PASSWORD_DEFAULT);
            $id = $this->create($values['email'], $hash, $values['nom']);

            $user = [
                'id' => $id,
                'email' => $values['email'],
                'nom' => $values['nom']
            ];
        } else {

            // Vérification mot de passe
            // if (!password_verify($mdp, $user['mdp'])) {
            //     return [
            //         'ok' => false,
            //         'errors' => ['mdp' => "Mot de passe incorrect."],
            //         'values' => $values
            //     ];
            // }

            if ($user['mdp'] !== $mdp && !password_verify($mdp, $user['mdp'])) {
                return [
                    'ok' => false,
                    'errors' => ['mdp' => "Mot de passe incorrect."],
                    'values' => $values
                ];
            }
        }

        return [
            'ok' => true,
            'user' => $user,
            'errors' => [],
            'values' => []
        ];
    }

    private function findByEmail(string $email): ?array
    {

        $st = $this->db->prepare(
            "SELECT id, email, nom, mdp 
             FROM User 
             WHERE email = ? 
             LIMIT 1"
        );

        $st->execute([$email]);
        $res = $st->fetch(PDO::FETCH_ASSOC);

        return $res ?: null;
    }

    private function create(string $email, string $hash, string $nom): int
    {

        $st = $this->db->prepare(
            "INSERT INTO User (email, mdp, nom)
             VALUES (?, ?, ?)"
        );

        $st->execute([$email, $hash, $nom]);

        return (int)$this->db->lastInsertId();
    }
}
