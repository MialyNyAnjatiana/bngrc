<?php

namespace app\controllers;

use app\controllers\VilleController;
use app\models\UserModel;
use Flight;

class UserController
{

    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public static function showLogin()
    {
        // Flight::render('login', [
        //     'values' => ['email' => '', 'nom' => ''],
        //     'errors' => [],
        //     'success' => false
        // ]);
        VilleController::home();
    }

    public static function showHome()
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user'])) {
            Flight::redirect('/');
            return;
        }

        // Flight::render('home');
        VilleController::home();
    }


    public static function postLogin()
    {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $db = Flight::db();
        $userModel = new UserModel($db);
        $req = Flight::request();

        $email = $req->data->email ?? '';
        $nom = $req->data->nom ?? '';
        $mdp = $req->data->mdp ?? '';

        $res = $userModel->autoLogin([
            'email' => $email,
            'nom'   => $nom,
            'mdp'   => $mdp
        ]);

        if (!$res['ok']) {
            Flight::render('login', [
                'errors' => $res['errors'],
                'values' => $res['values']
            ]);
            return;
        }

        $_SESSION['user'] = [
            'id' => $res['user']['id'],
            'nom' => $res['user']['nom'],
            'email' => $res['user']['email']
        ];

        Flight::redirect('/home');
    }
}
