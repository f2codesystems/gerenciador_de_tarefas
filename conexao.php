<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db = "todolist";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Erro na conexão: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8mb4")) {
    die("Erro ao definir charset: " . $conn->error);
}
?>
