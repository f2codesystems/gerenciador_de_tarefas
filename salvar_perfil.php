<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$telefone = $_POST['telefone'] ?? '';
$nivel = $_POST['nivel'] ?? '';

$nivel = ($nivel === 'administrador') ? 'admin' : 'usuario';  // garante valor válido

// Atualiza telefone e nível
$stmt = $conn->prepare("UPDATE usuarios SET telefone = ?, nivel = ? WHERE id = ?");
$stmt->bind_param("ssi", $telefone, $nivel, $usuario_id);

if ($stmt->execute()) {
    $stmt->close();
    header("Location: perfil.php?msg=sucesso");
    exit;
} else {
    $stmt->close();
    header("Location: perfil.php?msg=erro");
    exit;
}
