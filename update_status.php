<?php
include 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id'] ?? 0);
    $status = $_POST['status'] ?? '';

    $status_permitidos = ['todo', 'inprogress', 'done'];
    if (!in_array($status, $status_permitidos)) {
        http_response_code(400);
        echo "Status inválido.";
        exit;
    }

    $stmt = $conn->prepare("UPDATE tarefas SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo "Status atualizado com sucesso.";
    } else {
        echo "Nenhuma tarefa atualizada.";
    }

    $stmt->close();
}
?>
