<?php
session_start();
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';
$usuario_id = $_SESSION['usuario_id'];

// Dados do usuário
$stmt = $conn->prepare("SELECT nome, foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($usuario_nome, $usuario_foto);
$stmt->fetch();
$stmt->close();

// Tarefas por status
$statuses = ['todo' => 'A Fazer', 'inprogress' => 'Em Progresso', 'done' => 'Concluído'];
$tarefas = [];
foreach ($statuses as $status => $titulo) {
    $stmt = $conn->prepare("SELECT * FROM tarefas WHERE usuario_id = ? AND status = ? ORDER BY ordem ASC");
    $stmt->bind_param("is", $usuario_id, $status);
    $stmt->execute();
    $result = $stmt->get_result();
    $tarefas[$status] = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Notificações de prazo
$hoje = date('Y-m-d');
$data_limite = date('Y-m-d', strtotime('+3 days'));
$stmt_notificacoes = $conn->prepare("
    SELECT id FROM tarefas 
    WHERE usuario_id = ? 
      AND status != 'done' 
      AND prazo_final IS NOT NULL
      AND (prazo_final < ? OR prazo_final BETWEEN ? AND ?)
");
$stmt_notificacoes->bind_param("ssss", $usuario_id, $hoje, $hoje, $data_limite);
$stmt_notificacoes->execute();
$result_notificacoes = $stmt_notificacoes->get_result();
$notificacoes_count = $result_notificacoes->num_rows;
$stmt_notificacoes->close();

// Saudação com base na hora
$hora = (int)date('H');
$saudacao = $hora < 12 ? "Bom dia" : ($hora < 18 ? "Boa tarde" : "Boa noite");

function prazoStatusIconTexto($prazo_final, $status) {
    $hoje = date('Y-m-d');
    $proximo = date('Y-m-d', strtotime('+3 days'));
    if ($status === 'done') {
        return '<i class="fas fa-check-circle text-success prazo-icon" title="Concluído"></i><span class="prazo-text">Concluído</span>';
    }
    if (!$prazo_final) return '';
    if ($prazo_final < $hoje) {
        return '<i class="fas fa-calendar-times text-danger prazo-icon" title="Vencido"></i><span class="prazo-text">Vencido</span>';
    } elseif ($prazo_final >= $hoje && $prazo_final <= $proximo) {
        return '<i class="fas fa-hourglass-half text-warning prazo-icon" title="Próximo"></i><span class="prazo-text">Próximo</span>';
    } else {
        return '<i class="fas fa-calendar-check text-success prazo-icon" title="No Prazo"></i><span class="prazo-text">No Prazo</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Gerenciador de Tarefas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f0f2f5; margin: 0; padding: 0; }
    .navbar { background-color: #026aa7; }
    .sidebar {
      height: 100vh; width: 220px; position: fixed; top: 0; left: 0; background-color: #003f5c;
      padding-top: 60px; overflow-x: hidden; z-index: 1001;
    }
    .sidebar a {
      padding: 12px 20px; display: flex; align-items: center; gap: 10px;
      color: #fff; text-decoration: none; transition: background 0.3s;
    }
    .sidebar a:hover { background-color: #2a5470; }
    .main-content { margin-left: 220px; padding: 20px; }
    .kanban-board {
      display: flex; gap: 1rem; overflow-x: auto; flex-wrap: nowrap;
    }
    .kanban-column {
      background: #f8f9fa; border-radius: 8px; padding: 0.5rem; min-width: 280px;
      flex: 1 1 280px; box-shadow: 0 2px 6px rgba(0,0,0,0.15);
      max-height: 75vh; overflow-y: auto;
    }
    .kanban-task {
      background: white; border-radius: 4px; padding: 0.5rem; margin-bottom: 0.5rem;
      cursor: grab; box-shadow: 0 1px 3px rgba(0,0,0,0.2);
      position: relative; border: 1px solid #ddd;
    }
    .prioridade-baixa { border-left: 5px solid #28a745; }
    .prioridade-média { border-left: 5px solid #ffc107; }
    .prioridade-alta { border-left: 5px solid #dc3545; }
    .task-actions {
      position: absolute; top: 5px; right: 5px;
      display: flex; gap: 4px; align-items: center;
    }
    .task-actions button {
      border: none; background: none; font-size: 1rem; cursor: pointer; color: #444; padding: 0 4px;
    }
    .btn-concluir { color: #28a745; font-size: 1.2rem; }
    .badge-notify {
      background: #dc3545; color: white; font-size: 0.75rem; font-weight: bold;
      padding: 2px 6px; border-radius: 50%; position: absolute; top: 10px; right: 15px; z-index: 15;
    }
    .top-kanban-alert {
      background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 6px;
      padding: 12px 16px; margin-bottom: 15px; color: #856404; font-weight: 600; cursor: pointer;
    }
    .prazo-icon { margin-right: 5px; cursor: default; }
    .prazo-text { font-weight: 600; font-size: 0.85rem; vertical-align: middle; margin-left: 3px; }
    .badge-status {
      font-weight: 600; padding: 0.25em 0.5em; border-radius: 0.375rem; font-size: 0.85rem;
      display: inline-block; margin-bottom: 6px;
    }
  </style>
</head>
<body>

<div class="sidebar">
  <a href="index.php"><i class="fas fa-home"></i> Home</a>
  <a href="usuarios.php"><i class="fas fa-users"></i> Usuários</a>
  <a href="tarefas.php"><i class="fas fa-tasks"></i> Tarefas Totais</a>
  <a href="tarefas.php?status=todo"><i class="fas fa-list"></i> A Fazer</a>
  <a href="tarefas.php?status=inprogress"><i class="fas fa-spinner"></i> Em Progresso</a>
  <a href="tarefas.php?status=done"><i class="fas fa-check"></i> Concluídas</a>
  <a href="notificacoes.php" style="position: relative;">
    <i class="fas fa-bell"></i> Notificações
    <?php if ($notificacoes_count > 0): ?>
      <span class="badge-notify"><?= $notificacoes_count ?></span>
    <?php endif; ?>
  </a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a>
</div>

<nav class="navbar navbar-dark px-3 fixed-top">
  <span class="navbar-brand mb-0 h1">Gerenciador de Tarefas</span>
  <div class="d-flex gap-2">
    <a href="perfil.php" class="btn btn-light btn-sm">Meu Perfil</a>
    <a href="relatorios.php" class="btn btn-light btn-sm">Relatórios</a>
    <a href="configuracoes.php" class="btn btn-light btn-sm">Configurações</a>
    <div class="d-flex align-items-center gap-2">
      <span class="text-white"><?= $saudacao ?>, <?= htmlspecialchars($usuario_nome) ?>!</span>
      <a href="perfil.php">
        <?php if ($usuario_foto): ?>
          <img src="uploads/<?= htmlspecialchars($usuario_foto) ?>" class="rounded-circle" width="32" height="32" />
        <?php else: ?>
          <img src="https://via.placeholder.com/32" class="rounded-circle" />
        <?php endif; ?>
      </a>
    </div>
  </div>
</nav>

<div class="main-content mt-5 pt-3">
  <?php if (!empty($_SESSION['mensagem_sucesso'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <?= htmlspecialchars($_SESSION['mensagem_sucesso']) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['mensagem_sucesso']); ?>
  <?php endif; ?>

  <?php if ($notificacoes_count > 0): ?>
    <div class="top-kanban-alert" onclick="window.location.href='notificacoes.php'">
      Atenção! Alerta de prazos próximos ou vencidos! Clique para ver notificações!
    </div>
  <?php endif; ?>

  <!-- Botões de ação -->
  <div class="mb-3">
    <a href="criar_tarefa.php" class="btn btn-primary">
      <i class="fas fa-plus"></i> Nova Tarefa
    </a>
    <button onclick="importarGoogle()" class="btn btn-success">
      <i class="fas fa-cloud-download-alt"></i> Importar do Google
    </button>
  </div>

  <div class="mb-4">
    <input type="text" id="pesquisaTarefa" class="form-control" placeholder="🔍 Pesquisar tarefas...">
  </div>

  <div id="alerta-importacao" class="alert alert-info d-none" role="alert"></div>

  <div class="kanban-board" id="kanban-board">
    <?php foreach ($statuses as $status_key => $status_name): ?>
      <div class="kanban-column" ondrop="drop(event)" ondragover="allowDrop(event)" data-status="<?= $status_key ?>">
        <h5><?= $status_name ?></h5>
        <?php foreach ($tarefas[$status_key] as $t): ?>
          <?php
            $status_label = [
              'todo' => ['A Fazer', 'danger'],
              'inprogress' => ['Em Progresso', 'warning'],
              'done' => ['Concluído', 'success']
            ];
            [$texto_etiqueta, $cor_etiqueta] = $status_label[$status_key];
            $prazo_icon_html = prazoStatusIconTexto($t['prazo_final'], $t['status']);
          ?>
          <div class="kanban-task 
            <?= $t['prioridade'] === 'Baixa' ? 'prioridade-baixa' : ($t['prioridade'] === 'Média' ? 'prioridade-média' : 'prioridade-alta') ?>"
            id="tarefa-<?= $t['id'] ?>"
            draggable="true"
            ondragstart="drag(event)"
            data-id="<?= $t['id'] ?>"
            data-status="<?= $t['status'] ?>"
            style="background-color: <?= $t['status'] === 'inprogress' ? '#fffbe6' : ($t['status'] === 'done' ? '#e6ffed' : '#fff') ?>;"
          >
            <!-- Etiqueta de status -->
            <span class="badge badge-status bg-<?= $cor_etiqueta ?>"><?= $texto_etiqueta ?></span>
            <!-- Título da tarefa abaixo da etiqueta -->
            <div><strong><?= htmlspecialchars($t['titulo']) ?></strong></div>

            <!-- Ícone prazo + texto -->
            <div title="Prazo" style="display:flex; align-items:center;">
              <?= $prazo_icon_html ?>
            </div>

            <div class="task-actions">
              <?php if (!empty($t['descricao'])): ?>
                <button onclick="editarTarefa(<?= $t['id'] ?>)" title="Ver descrição">
                  <i class="fas fa-align-left"></i>
                </button>
              <?php endif; ?>

              <?php if ($status_key === 'inprogress'): ?>
                <button class="btn-concluir" onclick="concluirTarefa(<?= $t['id'] ?>)" title="Concluir"><i class="fas fa-check"></i></button>
              <?php endif; ?>
              <button onclick="editarTarefa(<?= $t['id'] ?>)" title="Editar">✏️</button>
              <button onclick="excluirTarefa(<?= $t['id'] ?>)" title="Excluir">🗑️</button>
            </div>

            <small>Prioridade: <?= htmlspecialchars($t['prioridade']) ?></small><br>
            <?php if (!empty($t['categoria'])): ?>
              <small>Categoria: <?= htmlspecialchars($t['categoria']) ?></small>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  function allowDrop(ev) { ev.preventDefault(); }
  function drag(ev) { ev.dataTransfer.setData("text", ev.target.id); }

  function drop(ev) {
    ev.preventDefault();
    const data = ev.dataTransfer.getData("text");
    const task = document.getElementById(data);
    const newStatus = ev.currentTarget.getAttribute("data-status");
    const taskId = task.getAttribute("data-id");

    ev.currentTarget.appendChild(task);
    task.setAttribute("data-status", newStatus);
    task.style.backgroundColor = newStatus === 'inprogress' ? '#fffbe6' : (newStatus === 'done' ? '#e6ffed' : '#fff');

    // Atualiza a etiqueta de status visualmente sem recarregar a página
    const badge = task.querySelector('.badge-status');
    if (badge) {
      if (newStatus === 'todo') {
        badge.textContent = 'A Fazer';
        badge.className = 'badge badge-status bg-danger';
      } else if (newStatus === 'inprogress') {
        badge.textContent = 'Em Progresso';
        badge.className = 'badge badge-status bg-warning';
      } else if (newStatus === 'done') {
        badge.textContent = 'Concluído';
        badge.className = 'badge badge-status bg-success';
      }
    }

    // Atualiza ícone e texto de prazo
    const prazoDiv = task.querySelector('[title="Prazo"]');
    let prazoHtml = '';
    if (newStatus === 'done') {
      prazoHtml = '<i class="fas fa-check-circle text-success prazo-icon" title="Concluído"></i><span class="prazo-text">Concluído</span>';
    } else {
      // Aqui poderia atualizar conforme o prazo real, mas simplificado:
      prazoHtml = '<i class="fas fa-calendar-check text-success prazo-icon" title="No Prazo"></i><span class="prazo-text">No Prazo</span>';
    }
    prazoDiv.innerHTML = prazoHtml;

    // Controla visibilidade do botão concluir
    const btnConcluir = task.querySelector('.btn-concluir');
    if (btnConcluir) {
      btnConcluir.style.display = newStatus === 'inprogress' ? 'inline-block' : 'none';
    }

    // Remove borda colorida lateral no "concluído"
    if (newStatus === 'done') {
      task.classList.remove('prioridade-baixa', 'prioridade-média', 'prioridade-alta');
      task.style.borderLeft = '1px solid #ddd';
    } else {
      // Reaplica borda conforme prioridade
      const prioridade = task.querySelector('small').textContent.toLowerCase();
      task.classList.remove('prioridade-baixa', 'prioridade-média', 'prioridade-alta');
      if (prioridade.includes('baixa')) task.classList.add('prioridade-baixa');
      else if (prioridade.includes('média')) task.classList.add('prioridade-média');
      else task.classList.add('prioridade-alta');
    }

    // Atualiza status no banco via fetch API
    fetch('update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${taskId}&status=${newStatus}`
    });
  }

  function editarTarefa(id) {
    location.href = `editar_tarefa.php?id=${id}`;
  }

  function excluirTarefa(id) {
    if (confirm("Tem certeza que deseja excluir esta tarefa?")) {
      location.href = `deletar_tarefa.php?id=${id}`;
    }
  }

  function concluirTarefa(id) {
    fetch('update_status.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `id=${id}&status=done`
    }).then(() => location.reload());
  }

  // Pesquisa rápida nas tarefas exibidas
  document.getElementById("pesquisaTarefa").addEventListener("input", function () {
    const termo = this.value.toLowerCase().trim();
    document.querySelectorAll(".kanban-task").forEach(task => {
      const textoBusca = task.textContent.toLowerCase();
      task.style.display = textoBusca.includes(termo) ? "block" : "none";
    });
  });

  function importarGoogle() {
    const alerta = document.getElementById('alerta-importacao');
    alerta.classList.remove('alert-danger', 'alert-info');
    alerta.classList.add('alert-info');
    alerta.textContent = 'Importando eventos...';
    alerta.classList.remove('d-none');

    fetch('importar_google.php')
      .then(res => res.json())
      .then(data => {
        if (data.erro) {
          alerta.textContent = data.erro;
          alerta.classList.remove('alert-info');
          alerta.classList.add('alert-danger');
        } else {
          alerta.textContent = `${data.importados} evento(s) importado(s) com sucesso!`;
        }
        setTimeout(() => alerta.classList.add('d-none'), 5000);
      })
      .catch(err => {
        alerta.textContent = 'Erro ao importar eventos.';
        alerta.classList.remove('alert-info');
        alerta.classList.add('alert-danger');
        console.error(err);
      });
  }
</script>
</body>
</html>
