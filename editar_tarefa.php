<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

$id = $_GET['id'] ?? null;
$usuario_id = $_SESSION['usuario_id'];

if (!$id || !is_numeric($id)) {
    header("Location: index.php");
    exit;
}

// Buscar categorias para o select
$categorias = [];
$res = $conn->query("SELECT id, nome FROM categorias ORDER BY nome ASC");
if ($res) {
    $categorias = $res->fetch_all(MYSQLI_ASSOC);
}

// Carrega tarefa
$stmt = $conn->prepare("SELECT * FROM tarefas WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $id, $usuario_id);
$stmt->execute();
$result = $stmt->get_result();
$tarefa = $result->fetch_assoc();

if (!$tarefa) {
    header("Location: index.php");
    exit;
}

$msg = '';
$tipo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descricao = trim($_POST['descricao']);
    $prazo_inicial_raw = $_POST['prazo_inicial'] ?? null;
    $prazo_final_raw = $_POST['prazo_final'] ?? null;
    $prioridade = $_POST['prioridade'];
    $status = $_POST['status'];
    $categoria_id = $_POST['categoria_id'];

    if (!$categoria_id || !is_numeric($categoria_id)) {
        $msg = "Categoria inválida!";
        $tipo = "danger";
    } elseif ($titulo === '') {
        $msg = "Título é obrigatório!";
        $tipo = "danger";
    } elseif (!$prazo_inicial_raw || !$prazo_final_raw) {
        $msg = "Prazo inicial e final são obrigatórios!";
        $tipo = "danger";
    } else {
        // Ajustar timezone e formatação correta das datas
        $timezone = new DateTimeZone('America/Sao_Paulo');
        $dt_start = DateTime::createFromFormat('Y-m-d\TH:i', $prazo_inicial_raw, $timezone);
        $dt_end = DateTime::createFromFormat('Y-m-d\TH:i', $prazo_final_raw, $timezone);

        if (!$dt_start || !$dt_end) {
            $msg = "Formato de data/hora inválido!";
            $tipo = "danger";
        } else {
            $prazo_inicial = $dt_start->format('Y-m-d H:i:s');
            $prazo_final = $dt_end->format('Y-m-d H:i:s');

            $stmt = $conn->prepare("UPDATE tarefas SET titulo=?, descricao=?, prazo_inicial=?, prazo_final=?, prioridade=?, status=?, categoria_id=? WHERE id=? AND usuario_id=?");
            $stmt->bind_param("ssssssiii", $titulo, $descricao, $prazo_inicial, $prazo_final, $prioridade, $status, $categoria_id, $id, $usuario_id);

            if ($stmt->execute()) {
                // Integração com Google Calendar
                require_once 'vendor/autoload.php';

                if (isset($_SESSION['access_token'])) {
                    $client = new Google_Client();
                    $client->setAccessToken($_SESSION['access_token']);

                    if (!$client->isAccessTokenExpired()) {
                        $service = new Google_Service_Calendar($client);

                        // Buscar o event_id da tarefa para atualizar no Google Calendar
                        $stmtEvento = $conn->prepare("SELECT event_id FROM tarefas WHERE id = ?");
                        $stmtEvento->bind_param("i", $id);
                        $stmtEvento->execute();
                        $stmtEvento->bind_result($evento_google_id);
                        $stmtEvento->fetch();
                        $stmtEvento->close();

                        if ($evento_google_id) {
                            try {
                                $event = $service->events->get('primary', $evento_google_id);

                                $event->setSummary($titulo);
                                $event->setDescription($descricao);

                                $start = new Google_Service_Calendar_EventDateTime();
                                $start->setDateTime($dt_start->format(DateTime::ATOM));
                                $start->setTimeZone('America/Sao_Paulo');
                                $event->setStart($start);

                                $end = new Google_Service_Calendar_EventDateTime();
                                $end->setDateTime($dt_end->format(DateTime::ATOM));
                                $end->setTimeZone('America/Sao_Paulo');
                                $event->setEnd($end);

                                $service->events->update('primary', $event->getId(), $event);
                            } catch (Exception $e) {
                                error_log("Erro ao atualizar evento no Google Calendar: " . $e->getMessage());
                            }
                        }
                    }
                }
                header("Location: index.php");
                exit;
            } else {
                $msg = "Erro ao atualizar tarefa!";
                $tipo = "danger";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Tarefa</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
  <h2>Editar Tarefa</h2>
  <?php if (!empty($msg)): ?>
    <div class="alert alert-<?= $tipo ?>"><?= htmlspecialchars($msg) ?></div>
  <?php endif; ?>
  <form method="POST">
    <div class="mb-3">
      <label class="form-label">Título</label>
      <input type="text" name="titulo" class="form-control" value="<?= htmlspecialchars($tarefa['titulo']) ?>" required>
    </div>
    <div class="mb-3">
      <label class="form-label">Descrição</label>
      <textarea name="descricao" class="form-control"><?= htmlspecialchars($tarefa['descricao']) ?></textarea>
    </div>
    <div class="mb-3">
      <label class="form-label">Categoria</label>
      <select name="categoria_id" class="form-select" required>
        <option value="">Selecione uma categoria</option>
        <?php foreach ($categorias as $cat): ?>
          <option value="<?= $cat['id'] ?>" <?= $tarefa['categoria_id'] == $cat['id'] ? 'selected' : '' ?>>
            <?= htmlspecialchars($cat['nome']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Prazo Inicial</label>
      <input type="datetime-local" name="prazo_inicial" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($tarefa['prazo_inicial'])) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Prazo Final</label>
      <input type="datetime-local" name="prazo_final" class="form-control" required value="<?= date('Y-m-d\TH:i', strtotime($tarefa['prazo_final'])) ?>">
    </div>
    <div class="mb-3">
      <label class="form-label">Prioridade</label>
      <select name="prioridade" class="form-select">
        <option <?= $tarefa['prioridade']=='Baixa'?'selected':'' ?>>Baixa</option>
        <option <?= $tarefa['prioridade']=='Média'?'selected':'' ?>>Média</option>
        <option <?= $tarefa['prioridade']=='Alta'?'selected':'' ?>>Alta</option>
      </select>
    </div>
    <div class="mb-3">
      <label class="form-label">Status</label>
      <select name="status" class="form-select">
        <option value="todo" <?= $tarefa['status']=='todo'?'selected':'' ?>>A Fazer</option>
        <option value="inprogress" <?= $tarefa['status']=='inprogress'?'selected':'' ?>>Em Progresso</option>
        <option value="done" <?= $tarefa['status']=='done'?'selected':'' ?>>Concluído</option>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Salvar</button>
    <a href="index.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
