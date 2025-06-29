<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

session_start();
include 'conexao.php';

$client = new Google_Client();
$client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
$client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
$client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
$client->addScope(Google_Service_Calendar::CALENDAR_EVENTS);
$client->addScope('email');
$client->addScope('profile');

if (!isset($_GET['code'])) {
    echo "Erro: código de autorização não recebido.";
    exit;
}

// Troca o código por um token
$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    echo "Erro ao obter token: " . htmlspecialchars($token['error']);
    exit;
}

$_SESSION['access_token'] = $token;

// Obter informações do usuário Google
$client->setAccessToken($token);
$oauth2 = new Google_Service_Oauth2($client);
$googleUser = $oauth2->userinfo->get();

$nome = $googleUser->name;
$email = $googleUser->email;
$foto = $googleUser->picture;

// Verifica se já existe
$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $senha_random = bin2hex(random_bytes(10));
    $senha_hash = password_hash($senha_random, PASSWORD_DEFAULT);

    $stmtInsert = $conn->prepare("INSERT INTO usuarios (nome, email, senha, foto) VALUES (?, ?, ?, ?)");
    $stmtInsert->bind_param("ssss", $nome, $email, $senha_hash, $foto);
    $stmtInsert->execute();
    $usuario_id = $stmtInsert->insert_id;
} else {
    $stmt->bind_result($usuario_id);
    $stmt->fetch();
}

$_SESSION['usuario_id'] = $usuario_id;

// Redireciona para o painel principal
header("Location: index.php");
exit;
