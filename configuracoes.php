<?php
include 'funcoes.php';
verificar_login();
include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];
$usuario = obter_dados_usuario($conn, $usuario_id);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Configurações</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
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
  </style>
</head>
<body>

<!-- Sidebar -->
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

<!-- Navbar -->
<nav class="navbar navbar-dark px-3 fixed-top">
  <span class="navbar-brand mb-0 h1">Gerenciador de Tarefas</span>
  <div class="d-flex gap-2">
    <a href="perfil.php" class="btn btn-light btn-sm">Meu Perfil</a>
    <a href="relatorios.php" class="btn btn-light btn-sm">Relatórios</a>
    <a href="configuracoes.php" class="btn btn-light btn-sm">Configurações</a>
    <div class="d-flex align-items-center gap-2">
      <span class="text-white"><?= saudacao() ?>, <?= escape($usuario['nome']) ?>!</span>
      <a href="perfil.php">
        <?php if ($usuario['foto']): ?>
          <img src="uploads/<?= escape($usuario['foto']) ?>" class="rounded-circle" width="32" height="32" alt="Foto Perfil">
        <?php else: ?>
          <img src="https://via.placeholder.com/32" class="rounded-circle" alt="Sem Foto">
        <?php endif; ?>
      </a>
    </div>
  </div>
</nav>

<!-- Conteúdo Principal -->
<div class="main-content mt-5 pt-3">
  <h2><i class="fas fa-gear me-2"></i>Configurações do Sistema</h2>
  <div class="row mt-4 g-3">

    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-user-cog me-2"></i><?= $usuario['nivel'] === 'administrador' ? 'Usuários' : 'Usuário' ?></h5>
          <p class="card-text">
            <?= $usuario['nivel'] === 'administrador' 
              ? 'Edite os dados cadastrais dos usuários do sistema.' 
              : 'Edite seus dados cadastrais de usuário do sistema.' ?>
          </p>
          <a href="usuarios.php" class="btn btn-primary"><?= $usuario['nivel'] === 'administrador' ? 'Gerenciar Usuários' : 'Gerenciar Usuário' ?></a>
        </div>
      </div>
    </div>

    <?php if ($usuario['nivel'] === 'administrador'): ?>
    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-user-shield me-2"></i>Permissões</h5>
          <p class="card-text">Altere os níveis de acesso (Usuário ou Administrador).</p>
          <a href="permissoes.php" class="btn btn-secondary">Gerenciar Permissões</a>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="card-title"><i class="fas fa-database me-2"></i>Backup</h5>
          <p class="card-text">Faça backup de todas as informações do sistema.</p>
          <a href="backup.php" class="btn btn-success">Fazer Backup</a>
        </div>
      </div>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
function toggleSidebar() {
  document.getElementById("sidebar").classList.toggle("collapsed");
}
</script>
</body>
</html>
