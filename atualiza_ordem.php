<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(["erro" => "Não autorizado"]);
    exit;
}

include 'conexao.php';

// Receber e validar o JSON
$dados = json_decode(file_get_contents('php://input'), true);

if (!$dados || !isset($dados['status']) || !isset($dados['ordem'])) {
    http_response_code(400);
    echo json_encode(["erro" => "Requisição inválida"]);
    exit;
}

$status = $dados['status'];
$ordem = $dados['ordem'];
$usuario_id = $_SESSION['usuario_id'];

$conn->begin_transaction();

try {
    foreach ($ordem as $item) {
        $id = intval($item['id']);
        $nova_ordem = intval($item['ordem']);

        $stmt = $conn->prepare("UPDATE tarefas SET status = ?, ordem = ? WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("siii", $status, $nova_ordem, $id, $usuario_id);
        $stmt->execute();
        $stmt->close();
    }
    $conn->commit();
    echo json_encode(["sucesso" => true]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(["erro" => "Erro ao atualizar ordem"]);
}
