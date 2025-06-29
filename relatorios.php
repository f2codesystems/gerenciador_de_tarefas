<?php
include 'funcoes.php';
verificar_login();

include 'conexao.php';
$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT nome, foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($usuario_nome, $usuario_foto);
$stmt->fetch();
$stmt->close();

$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim = $_POST['data_fim'] ?? '';
$status = $_POST['status'] ?? '';
$formato = $_POST['formato'] ?? 'excel';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Relatórios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
  <style>
    body { background-color: #f0f2f5; margin: 0; padding: 0; }
    .navbar { background-color: #026aa7; }
    .sidebar {
      height: 100vh;
      width: 220px;
      position: fixed;
      top: 0;
      left: 0;
      background-color: #003f5c;
      padding-top: 60px;
      transition: width 0.3s;
      overflow-x: hidden;
    }
    .sidebar a {
      padding: 12px 20px;
      display: block;
      color: #fff;
      text-decoration: none;
      transition: background 0.3s;
    }
    .sidebar a:hover {
      background-color: #2a5470;
    }
    .sidebar.collapsed {
      width: 60px;
    }
    .sidebar.collapsed a span {
      display: none;
    }
    .main-content {
      margin-left: 220px;
      padding: 20px;
      transition: margin-left 0.3s;
      min-height: 100vh;
    }
    .collapsed + .main-content {
      margin-left: 60px;
    }
    .toggle-btn {
      position: absolute;
      top: 10px;
      left: 230px;
      z-index: 1000;
      cursor: pointer;
      color: #fff;
      font-size: 18px;
      transition: left 0.3s;
    }
    .sidebar.collapsed ~ .toggle-btn {
      left: 70px;
    }
    .card-relatorio {
      border-radius: 12px;
      box-shadow: 0 4px 8px rgb(0 0 0 / 0.1);
      transition: transform 0.2s ease;
    }
    .card-relatorio:hover {
      transform: translateY(-6px);
      box-shadow: 0 8px 20px rgb(0 0 0 / 0.15);
    }
    .btn-export {
      width: 140px;
      margin-bottom: 8px;
      font-weight: 600;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .btn-excel {
      background-color: #2a9d8f;
      border: none;
      color: white;
    }
    .btn-excel:hover {
      background-color: #21867a;
      color: white;
    }
  </style>
</head>
<body>

<div class="sidebar" id="sidebar">
  <a href="index.php"><i class="fas fa-home"></i> <span>Home</span></a>
  <a href="usuarios.php"><i class="fas fa-users"></i> <span>Usuários</span></a>
  <a href="tarefas.php"><i class="fas fa-tasks"></i> <span>Tarefas Totais</span></a>
  <a href="tarefas.php?status=todo"><i class="fas fa-list"></i> <span>A Fazer</span></a>
  <a href="tarefas.php?status=inprogress"><i class="fas fa-spinner"></i> <span>Em Progresso</span></a>
  <a href="tarefas.php?status=done"><i class="fas fa-check"></i> <span>Concluídas</span></a>
  <a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Sair</span></a>
</div>
<div class="toggle-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></div>

<nav class="navbar navbar-dark px-3 fixed-top">
  <span class="navbar-brand mb-0 h1">Gerenciador de Tarefas</span>
  <div class="d-flex gap-2">
    <a href="perfil.php" class="btn btn-light btn-sm">Meu Perfil</a>
    <a href="relatorios.php" class="btn btn-light btn-sm">Relatórios</a>
    <a href="configuracoes.php" class="btn btn-light btn-sm">Configurações</a>
    <div class="d-flex align-items-center gap-2">
      <span class="text-white"><?= saudacao() ?>, <?= htmlspecialchars($usuario_nome) ?>!</span>
      <a href="perfil.php">
        <?php if ($usuario_foto): ?>
          <img src="uploads/<?= htmlspecialchars($usuario_foto) ?>" class="rounded-circle" width="32" height="32" alt="Foto Perfil" />
        <?php else: ?>
          <img src="https://via.placeholder.com/32" class="rounded-circle" alt="Sem Foto" />
        <?php endif; ?>
      </a>
    </div>
  </div>
</nav>

<div class="main-content mt-5 pt-3">
  <h2 class="mb-4"><i class="fas fa-chart-column me-2"></i>Relatórios</h2>

  <div class="container">

    <div class="card card-relatorio mb-4 p-4">
      <h4><i class="fas fa-tasks me-2 text-primary"></i>Relatórios de Tarefas</h4>
      <p>Filtre por período e status para exportar as tarefas:</p>

      <form method="post" action="export_tarefas.php" class="row g-3 align-items-center mb-3">
        <div class="col-md-3">
          <label for="data_inicio" class="form-label">Data Início</label>
          <input type="date" id="data_inicio" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" class="form-control" />
        </div>
        <div class="col-md-3">
          <label for="data_fim" class="form-label">Data Fim</label>
          <input type="date" id="data_fim" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" class="form-control" />
        </div>
        <div class="col-md-3">
          <label for="status" class="form-label">Status</label>
          <select name="status" id="status" class="form-select">
            <option value="" <?= $status === '' ? 'selected' : '' ?>>Todos</option>
            <option value="todo" <?= $status === 'todo' ? 'selected' : '' ?>>A Fazer</option>
            <option value="inprogress" <?= $status === 'inprogress' ? 'selected' : '' ?>>Em Progresso</option>
            <option value="done" <?= $status === 'done' ? 'selected' : '' ?>>Concluídas</option>
          </select>
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
          <button type="submit" name="formato" value="excel" class="btn btn-excel btn-export" title="Exportar Excel">
            <i class="fas fa-file-excel"></i> Exportar Excel
          </button>
        </div>
      </form>
    </div>

    <div class="card card-relatorio mb-4 p-4">
      <h4><i class="fas fa-bell me-2 text-warning"></i>Relatório de Notificações</h4>
      <p>Filtre por período para exportar as notificações:</p>

      <form method="post" action="export_notificacoes.php" class="row g-3 align-items-center">
        <div class="col-md-4">
          <label for="data_inicio_notif" class="form-label">Data Início</label>
          <input type="date" id="data_inicio_notif" name="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" class="form-control" />
        </div>
        <div class="col-md-4">
          <label for="data_fim_notif" class="form-label">Data Fim</label>
          <input type="date" id="data_fim_notif" name="data_fim" value="<?= htmlspecialchars($data_fim) ?>" class="form-control" />
        </div>
        <div class="col-md-4 d-flex align-items-end gap-2">
          <button type="submit" name="formato" value="excel" class="btn btn-excel btn-export" title="Exportar Excel">
            <i class="fas fa-file-excel"></i> Exportar Excel
          </button>
        </div>
      </form>
    </div>

  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>
</body>
</html>
