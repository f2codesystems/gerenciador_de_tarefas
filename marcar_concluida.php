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
    $stmt = $conn->prepare("UPDATE tarefas SET concluida = 1 WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Tarefa atualizada com sucesso
        header("Location: index.php?concluida=1");
    } else {
        // Nenhuma linha afetada (id não existe ou não pertence ao usuário)
        header("Location: index.php?erro=1");
    }

    $stmt->close();
} else {
    // ID inválido na URL
    header("Location: index.php?erro=1");
}
$conn->close();
exit;
?>
