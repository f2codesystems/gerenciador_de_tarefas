<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

include 'conexao.php';

$id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];

if ($id && is_numeric($id)) {
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Sucesso na exclusão
        header("Location: index.php?deletada=1");
    } else {
        // Tarefa não encontrada ou sem permissão
        header("Location: index.php?erro=1");
    }

    $stmt->close();
} else {
    // ID inválido
    header("Location: index.php?erro=1");
}
$conn->close();
exit;
?>
