<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

// Pega os dados do POST
$senha_atual = $_POST['senha_atual'] ?? '';
$nova_senha = $_POST['nova_senha'] ?? '';
$confirma_senha = $_POST['confirma_senha'] ?? '';

// Verifica campos vazios
if (!$senha_atual || !$nova_senha || !$confirma_senha) {
    $_SESSION['erro_senha'] = "Por favor, preencha todos os campos.";
    header("Location: perfil.php");
    exit;
}

// Busca a senha atual (hash) do usuário no banco
$stmt = $conn->prepare("SELECT senha FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($senha_hash);
if (!$stmt->fetch()) {
    $stmt->close();
    $_SESSION['erro_senha'] = "Usuário não encontrado.";
    header("Location: perfil.php");
    exit;
}
$stmt->close();

// Verifica se a senha atual bate com o hash armazenado
if (!password_verify($senha_atual, $senha_hash)) {
    $_SESSION['erro_senha'] = "Senha atual incorreta.";
    header("Location: perfil.php");
    exit;
}

// Verifica se a nova senha e a confirmação são iguais
if ($nova_senha !== $confirma_senha) {
    $_SESSION['erro_senha'] = "A nova senha e a confirmação não coincidem.";
    header("Location: perfil.php");
    exit;
}

// Atualiza a senha no banco (hash da nova senha)
$nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

$stmt = $conn->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
$stmt->bind_param("si", $nova_senha_hash, $usuario_id);

if ($stmt->execute()) {
    $_SESSION['sucesso_senha'] = "Senha alterada com sucesso!";
} else {
    $_SESSION['erro_senha'] = "Erro ao atualizar a senha. Tente novamente.";
}

$stmt->close();
$conn->close();

// Redireciona para perfil com mensagens
header("Location: perfil.php");
exit;
?>
