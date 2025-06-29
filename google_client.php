<?php
require_once 'vendor/autoload.php';

use Google_Client;

function getGoogleClient(bool $requireToken = true): ?Google_Client {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    static $client = null;
    if ($client) return $client;

    // Carrega variáveis do .env
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->safeLoad();

    $client = new Google_Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID'] ?? '');
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET'] ?? '');
    $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI'] ?? '');
    $client->addScope([
        'email',
        'profile'
    ]);

    // Permite uso do client antes do login (sem access_token)
    if ($requireToken) {
        if (!isset($_SESSION['access_token'])) {
            return null;
        }
        $client->setAccessToken($_SESSION['access_token']);

        if ($client->isAccessTokenExpired()) {
            unset($_SESSION['access_token']);
            return null;
        }
    }

    return $client;
}
