<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

include 'conexao.php';
require_once 'google_client.php'; // seu helper para Google Client

// Buscar categorias para o select
$categorias = [];
$res = $conn->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
if ($res) {
    $categorias = $res->fetch_all(MYSQLI_ASSOC);
}

$msg = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $prazo_inicial_raw = $_POST['prazo_inicial'] ?? null;
    $prazo_final_raw = $_POST['prazo_final'] ?? null;
    $prioridade = $_POST['prioridade'] ?? 'Baixa';
    $status = $_POST['status'] ?? 'todo';
    $categoria_id = $_POST['categoria_id'] ?? null;
    $usuario_id = $_SESSION['usuario_id'];
    $event_id = null;

    if ($titulo === '') {
        $msg = "Título é obrigatório!";
        $tipo = "danger";
    } elseif (!$categoria_id || !is_numeric($categoria_id)) {
        $msg = "Categoria inválida!";
        $tipo = "danger";
    } elseif (!$prazo_inicial_raw || !$prazo_final_raw) {
        $msg = "Prazo inicial e final são obrigatórios!";
        $tipo = "danger";
    } else {
        // Ajuste da conversão dos horários com timezone explícito
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $dt_start = DateTime::createFromFormat('Y-m-d\TH:i', $prazo_inicial_raw, $timezone);
        $dt_end = DateTime::createFromFormat('Y-m-d\TH:i', $prazo_final_raw, $timezone);

        if (!$dt_start || !$dt_end) {
            $msg = "Formato de data/hora inválido!";
            $tipo = "danger";
        } else {
            $prazo_inicial = $dt_start->format('Y-m-d H:i:s');
            $prazo_final = $dt_end->format('Y-m-d H:i:s');

            // Integração Google Calendar
            $client = getGoogleClient();
            if ($client) {
                try {
                    $service = new Google_Service_Calendar($client);

                    $event = new Google_Service_Calendar_Event([
                        'summary' => $titulo,
                        'description' => $descricao,
                        'start' => [
                            'dateTime' => $dt_start->format(DateTime::ATOM),
                            'timeZone' => 'America/Sao_Paulo',
                        ],
                        'end' => [
                            'dateTime' => $dt_end->format(DateTime::ATOM),
                            'timeZone' => 'America/Sao_Paulo',
                        ],
                    ]);

                    $calendarId = 'primary';
                    $createdEvent = $service->events->insert($calendarId, $event);
                    $event_id = $createdEvent->getId();

                } catch (Exception $e) {
                    error_log('Erro ao integrar com Google Calendar: ' . $e->getMessage());
                    $msg = "Erro ao integrar com Google Calendar!";
                    $tipo = "danger";
                }
            }

            if (!$msg) {
                $stmt = $conn->prepare("INSERT INTO tarefas (titulo, descricao, prazo_inicial, prazo_final, prioridade, status, usuario_id, categoria_id, event_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssssiis", $titulo, $descricao, $prazo_inicial, $prazo_final, $prioridade, $status, $usuario_id, $categoria_id, $event_id);

                if ($stmt->execute()) {
                    header("Location: index.php");
                    exit;
                } else {
                    $msg = "Erro ao criar tarefa!";
                    $tipo = "danger";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Criar Tarefa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>Criar Nova Tarefa</h2>
    <?php if (!empty($msg)): ?>
        <div class="alert alert-<?= $tipo ?>"><?= htmlspecialchars($msg) ?></div>
    <?php endif; ?>
    <form method="POST">
        <div class="mb-3">
            <label class="form-label">Título</label>
            <input type="text" name="titulo" class="form-control" required value="<?= htmlspecialchars($_POST['titulo'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Descrição</label>
            <textarea name="descricao" class="form-control"><?= htmlspecialchars($_POST['descricao'] ?? '') ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Categoria</label>
            <select name="categoria_id" class="form-select" required>
                <option value="">Selecione uma categoria</option>
                <?php foreach ($categorias as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (($_POST['categoria_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Prazo Inicial</label>
            <input type="datetime-local" name="prazo_inicial" class="form-control" required value="<?= htmlspecialchars($_POST['prazo_inicial'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Prazo Final</label>
            <input type="datetime-local" name="prazo_final" class="form-control" required value="<?= htmlspecialchars($_POST['prazo_final'] ?? '') ?>">
        </div>
        <div class="mb-3">
            <label class="form-label">Prioridade</label>
            <select name="prioridade" class="form-select">
                <option <?= (($_POST['prioridade'] ?? '') == 'Baixa') ? 'selected' : '' ?>>Baixa</option>
                <option <?= (($_POST['prioridade'] ?? '') == 'Média') ? 'selected' : '' ?>>Média</option>
                <option <?= (($_POST['prioridade'] ?? '') == 'Alta') ? 'selected' : '' ?>>Alta</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="todo" <?= (($_POST['status'] ?? '') == 'todo') ? 'selected' : '' ?>>A Fazer</option>
                <option value="inprogress" <?= (($_POST['status'] ?? '') == 'inprogress') ? 'selected' : '' ?>>Em Progresso</option>
                <option value="done" <?= (($_POST['status'] ?? '') == 'done') ? 'selected' : '' ?>>Concluído</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success">Criar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
</body>
</html>
