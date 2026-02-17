<?php

use app\controllers\DonsController;
use app\controllers\BesoinController;
use app\controllers\UserController;
use app\controllers\AchatController;
use app\controllers\RecapController;
use app\controllers\VenteController;
use app\controllers\LiaisonController;
use app\controllers\DataController;
use app\middlewares\SecurityHeadersMiddleware;
use flight\Engine;
use flight\net\Router;

Flight::set('flight.views.path', __DIR__ . '/../views');
/** 
 * @var Router $router 
 * @var Engine $app
 */

// This wraps all routes in the group with the SecurityHeadersMiddleware
$router->group('', function (Router $router) use ($app) {
    // DECONNEXION
    Flight::route('/exit', function () {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        session_unset();
        session_destroy();

        Flight::redirect('/');
    });


    // Accueil / login
    Flight::route('GET /', [UserController::class, 'showLogin']);
    // Flight::route('POST /login', [UserController::class, 'postLogin']);
    Flight::route('GET /home', [UserController::class, 'showHome']);

    // DONS
    Flight::route('GET /dons/sans-besoin', [DonsController::class, 'showFormDonSansBesoin']);
    Flight::route('POST /dons/sans-besoin', [DonsController::class, 'storeDonSansBesoin']);

    // Route pour afficher les dons par ville
    Flight::route('GET /dons/@idVille', [DonsController::class, 'getDons']);

    // BESOINS
    Flight::route('GET /besoins', [BesoinController::class, 'list']);
    Flight::route('GET /besoin/form', [BesoinController::class, 'form']);
    Flight::route('POST /besoin/add', [BesoinController::class, 'add']);


    // LIAISONS
    Flight::route('GET /liaison', [LiaisonController::class, 'showLiaisonSimple']);
    Flight::route('POST /liaison/lier', [LiaisonController::class, 'lierDon']);
    Flight::route('GET /api/besoins/ville/@idVille', [LiaisonController::class, 'getBesoinsParVille']);

    // ACHAT
    Flight::route('GET /achat', [AchatController::class, 'showAchatPage']);
    Flight::route('POST /achat/effectuer', [AchatController::class, 'effectuerAchat']);
    Flight::route('GET /api/achat/besoins/@idVille', [AchatController::class, 'getBesoinsParVille']);
    Flight::route('GET /api/achat/dons/@idVille', [AchatController::class, 'getDonsParVille']);

    // RECAP
    Flight::route('GET /recap', [RecapController::class, 'index']);
    Flight::route('GET /recap/data', [RecapController::class, 'data']);

    // VENTE
    Flight::route('GET /vente', [VenteController::class, 'showVentePage']);
    // Flight::route('GET /vente/details/@idDon', [VenteController::class, 'showVenteDetails']);
    Flight::route('GET /vente/vendre/@idDon', [VenteController::class, 'vendreDonInstantanee']);
    Flight::route('POST /vente/valeur', [VenteController::class, 'mettreAJourValeur']);
    Flight::route('POST /vente/pourcentage', [VenteController::class, 'mettreAJourPourcentage']);
    // Dans votre fichier de routes
    Flight::route('GET /api/vente/verifier/@idDon', [VenteController::class, 'verifierVente']);

    // Routes AJAX pour la vente
    Flight::route('GET /api/vente/historique/@idVille', [VenteController::class, 'getHistoriqueParVille']);

        // REINITIALISATION
    Flight::route('/reset', [DataController::class, 'reset']);
}, [SecurityHeadersMiddleware::class]);
