<?php
session_start();
require_once 'vendor/autoload.php';
require_once 'conexao.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Usuário não autenticado']);
    exit;
}

if (!isset($_SESSION['access_token'])) {
    echo json_encode(['erro' => 'Não autenticado com o Google. <a href=\'login_google.php\'>Clique aqui</a>']);
    exit;
}

try {
    $client = new Google_Client();
    $client->setClientId($_ENV['GOOGLE_CLIENT_ID']);
    $client->setClientSecret($_ENV['GOOGLE_CLIENT_SECRET']);
    $client->setRedirectUri($_ENV['GOOGLE_REDIRECT_URI']);
    $client->setAccessToken($_SESSION['access_token']);

    if ($client->isAccessTokenExpired()) {
        echo json_encode(['erro' => 'Token expirado. <a href="login_google.php">Faça login novamente</a>']);
        exit;
    }

    $service = new Google_Service_Calendar($client);
    $calendarId = 'primary';
    $optParams = [
        'maxResults' => 100,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => date('c'),
    ];

    $results = $service->events->listEvents($calendarId, $optParams);
    $eventos_google = $results->getItems();

    $importados = 0;
    $atualizados = 0;
    $ids_google = [];

    foreach ($eventos_google as $evento) {
        $google_id = $evento->getId();
        $ids_google[] = $google_id;

        $titulo = $evento->getSummary() ?: 'Sem título';
        $descricao = $evento->getDescription() ?: '';
        $start = $evento->getStart()->getDateTime() ?: $evento->getStart()->getDate();
        $end = $evento->getEnd()->getDateTime() ?: $evento->getEnd()->getDate();

        // Pega data e hora no formato completo
        $prazo_inicial = date('Y-m-d H:i:s', strtotime($start));
        $prazo_final = date('Y-m-d H:i:s', strtotime($end));

        $stmt_check = $conn->prepare("SELECT id, prazo_inicial, prazo_final FROM tarefas WHERE event_id = ? AND usuario_id = ?");
        $stmt_check->bind_param("si", $google_id, $_SESSION['usuario_id']);
        $stmt_check->execute();
        $stmt_check->store_result();
        $stmt_check->bind_result($id_existente, $db_prazo_inicial, $db_prazo_final);

        if ($stmt_check->num_rows === 0) {
            // Nova tarefa
            $prioridade = 'Média';
            $status = 'todo';
            $categoria_id = null;

            $stmt = $conn->prepare("INSERT INTO tarefas (titulo, descricao, prazo_inicial, prazo_final, prioridade, status, usuario_id, categoria_id, event_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssis", $titulo, $descricao, $prazo_inicial, $prazo_final, $prioridade, $status, $_SESSION['usuario_id'], $categoria_id, $google_id);
            $stmt->execute();
            $stmt->close();
            $importados++;
        } else {
            // Verifica se houve mudança nas datas
            $stmt_check->fetch();
            if ($prazo_inicial !== $db_prazo_inicial || $prazo_final !== $db_prazo_final) {
                $stmt_update = $conn->prepare("UPDATE tarefas SET prazo_inicial = ?, prazo_final = ? WHERE id = ?");
                $stmt_update->bind_param("ssi", $prazo_inicial, $prazo_final, $id_existente);
                $stmt_update->execute();
                $stmt_update->close();
                $atualizados++;
            }
        }

        $stmt_check->close();
    }

    // Remove tarefas que foram excluídas do Google Calendar
    $sql = "DELETE FROM tarefas WHERE usuario_id = ? AND event_id IS NOT NULL";
    if (count($ids_google) > 0) {
        $placeholders = implode(',', array_fill(0, count($ids_google), '?'));
        $sql .= " AND event_id NOT IN ($placeholders)";
    }

    $stmt = $conn->prepare($sql);
    if (count($ids_google) > 0) {
        $types = 'i' . str_repeat('s', count($ids_google));
        $params = array_merge([$_SESSION['usuario_id']], $ids_google);
        $bind_names[] = $types;
        foreach ($params as $i => $value) {
            $bind_names[] = &$params[$i];
        }
        call_user_func_array([$stmt, 'bind_param'], $bind_names);
    } else {
        $stmt->bind_param('i', $_SESSION['usuario_id']);
    }

    $stmt->execute();
    $excluidos = $stmt->affected_rows;
    $stmt->close();

    echo json_encode([
        'importados' => $importados,
        'atualizados' => $atualizados,
        'removidos' => $excluidos,
        'mensagem' => "$importados eventos importados, $atualizados atualizados, $excluidos removidos"
    ]);

} catch (Exception $e) {
    echo json_encode(['erro' => 'Erro ao importar eventos: ' . $e->getMessage()]);
}
