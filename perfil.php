<?php
include 'funcoes.php';
verificar_login();
include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$stmt = $conn->prepare("SELECT nome, email, foto, telefone, nivel FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nome, $email, $foto, $telefone, $nivel);
$stmt->fetch();
$stmt->close();

$nivel_desc = ($nivel === 'administrador') ? 'Administrador' : 'Usuário';
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Meu Perfil</title>
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
      <span class="text-white"><?= saudacao() ?>, <?= escape($nome) ?>!</span>
      <a href="perfil.php">
        <?php if ($foto): ?>
          <img src="uploads/<?= escape($foto) ?>" class="rounded-circle" width="32" height="32" alt="Foto Perfil" />
        <?php else: ?>
          <img src="https://via.placeholder.com/32" class="rounded-circle" alt="Sem Foto" />
        <?php endif; ?>
      </a>
    </div>
  </div>
</nav>

<!-- Conteúdo Principal -->
<div class="main-content mt-5 pt-3">
  <div class="container-fluid">
    <div class="row">

      <!-- Coluna esquerda - Dados do perfil -->
      <div class="col-md-6">

        <h2><i class="fas fa-user me-2"></i>Meu Perfil</h2>

        <div class="mb-3 mt-3">
          <?php if ($foto): ?>
            <img src="uploads/<?= escape($foto) ?>" class="rounded" width="150" alt="Foto do Usuário" />
          <?php else: ?>
            <img src="https://via.placeholder.com/150" class="rounded" alt="Sem Foto" />
          <?php endif; ?>
        </div>

        <!-- Formulário para atualizar foto -->
        <form action="salvar_foto.php" method="POST" enctype="multipart/form-data" class="mb-4">
          <div class="mb-3">
            <label for="foto" class="form-label">Atualizar Foto</label>
            <input type="file" class="form-control" name="foto" id="foto" required />
          </div>
          <button type="submit" class="btn btn-primary">Salvar</button>
        </form>

        <p><strong>Nome:</strong> <?= escape($nome) ?></p>
        <p><strong>Número de Telefone:</strong> <?= escape($telefone) ?></p>
        <p><strong>Email:</strong> <?= escape($email) ?></p>
        <p><strong>Nível:</strong> <?= $nivel_desc ?></p>

        <a href="index.php" class="btn btn-secondary mt-2">Voltar</a>
      </div>

      <!-- Coluna direita - Alterar senha -->
      <div class="col-md-6">
        <h2><i class="fas fa-key me-2"></i>Alterar Minha Senha</h2>
        <form action="alterar_senha.php" method="POST" class="mt-3">
          <div class="mb-3">
            <label for="senha_atual" class="form-label">Senha Atual</label>
            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required />
          </div>
          <div class="mb-3">
            <label for="nova_senha" class="form-label">Nova Senha</label>
            <input type="password" class="form-control" id="nova_senha" name="nova_senha" required />
          </div>
          <div class="mb-3">
            <label for="confirma_senha" class="form-label">Confirmar Senha</label>
            <input type="password" class="form-control" id="confirma_senha" name="confirma_senha" required />
          </div>
          <button type="submit" class="btn btn-warning">Alterar Senha</button>
        </form>
      </div>

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
