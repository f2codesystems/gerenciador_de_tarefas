<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

// Buscar nível do usuário
$stmtNivel = $conn->prepare("SELECT nivel FROM usuarios WHERE id = ?");
$stmtNivel->bind_param("i", $usuario_id);
$stmtNivel->execute();
$stmtNivel->bind_result($nivel_usuario);
$stmtNivel->fetch();
$stmtNivel->close();

// Se for administrador, pode ver todos
if ($nivel_usuario === 'administrador') {
    $result = $conn->query("SELECT id, nome, email, nivel FROM usuarios ORDER BY nome");
} else {
    $stmt = $conn->prepare("SELECT id, nome, email, nivel FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $result = $stmt->get_result();
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Usuários</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
  <h1 class="mb-4"><i class="fas fa-users me-2"></i>Lista de Usuários</h1>

  <table class="table table-bordered table-hover">
    <thead class="table-light">
      <tr>
        <th>ID</th>
        <th>Nome</th>
        <th>Email</th>
        <th>Nível</th>
        <th>Ações</th>
      </tr>
    </thead>
    <tbody>
      <?php while ($u = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $u['id'] ?></td>
          <td><?= htmlspecialchars($u['nome']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><?= htmlspecialchars($u['nivel'] ?? 'Usuário') ?></td>
          <td>
            <?php if ($nivel_usuario === 'administrador' || $u['id'] == $usuario_id): ?>
              <a href="editar_usuario.php?id=<?= $u['id'] ?>" class="btn btn-sm btn-primary">
                <i class="fas fa-edit"></i> Editar
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    </tbody>
  </table>

  <a href="configuracoes.php" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left"></i> Voltar</a>
</body>
</html>
