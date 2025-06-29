<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['titulo'])) {
    $titulo = $_POST['titulo'];
    $descricao = $_POST['descricao'] ?? '';
    $prazo = $_POST['prazo'] ?? null;
    $prioridade = $_POST['prioridade'] ?? 'Média';
    $categoria_id = $_POST['categoria'] ?? null;
    $usuario_id = $_SESSION['usuario_id'];

    $stmt = $conn->prepare("INSERT INTO tarefas (titulo, descricao, prazo, prioridade, categoria_id, usuario_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssii", $titulo, $descricao, $prazo, $prioridade, $categoria_id, $usuario_id);
    $stmt->execute();
} else {
    // Redireciona se tentar acessar sem POST
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Ação TCC</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="p-4">
<div class="alert alert-success">
  Tarefa adicionada com sucesso! <a href="index.php" class="alert-link">Voltar ao Kanban</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
