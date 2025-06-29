<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';
require_once 'vendor/autoload.php';
require_once 'google_client.php';

$id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];

if ($id && is_numeric($id)) {

    // Buscar o event_id antes de excluir a tarefa
    $stmt = $conn->prepare("SELECT event_id FROM tarefas WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();
    $stmt->bind_result($event_id);
    $stmt->fetch();
    $stmt->close();

    // Excluir do banco de dados
    $stmt = $conn->prepare("DELETE FROM tarefas WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $id, $usuario_id);
    $stmt->execute();
    $stmt->close();

    // Excluir evento do Google Calendar (se houver)
    if (!empty($event_id)) {
        $client = getGoogleClient();
        if ($client && !$client->isAccessTokenExpired()) {
            try {
                $service = new Google_Service_Calendar($client);
                $service->events->delete('primary', $event_id);
            } catch (Exception $e) {
                error_log("Erro ao excluir evento do Google Calendar: " . $e->getMessage());
            }
        }
    }

    // Define mensagem de sucesso
    $_SESSION['mensagem_sucesso'] = "Tarefa e evento excluídos com sucesso!";
}

header("Location: index.php");
exit;
?>
