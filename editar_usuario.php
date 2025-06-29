<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

$usuario_logado = $_SESSION['usuario_id'];

// Buscar nível do usuário logado
$stmtNivel = $conn->prepare("SELECT nivel FROM usuarios WHERE id = ?");
$stmtNivel->bind_param("i", $usuario_logado);
$stmtNivel->execute();
$stmtNivel->bind_result($nivel_logado);
$stmtNivel->fetch();
$stmtNivel->close();

// Validação do ID
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die("ID inválido.");
}

// Buscar dados do usuário a editar
$stmt = $conn->prepare("SELECT nome, email, telefone, nivel, foto FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($nome, $email, $telefone, $nivel, $foto);
$stmt->fetch();
$stmt->close();

// Atualização
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $novo_nome = trim($_POST['nome']);
    $novo_email = trim($_POST['email']);
    $novo_telefone = trim($_POST['telefone']);
    $foto_nome = $foto;

    // Se for admin, pode alterar o nível
    if ($nivel_logado === 'administrador') {
        $novo_nivel = $_POST['nivel'];
    } else {
        $novo_nivel = $nivel; // ignora o que vier do form
    }

    $nova_senha = trim($_POST['senha']);

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_nome = 'foto_' . time() . '.' . $ext;
        move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $foto_nome);
    }

    if (!empty($nova_senha)) {
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, senha = ?, telefone = ?, nivel = ?, foto = ? WHERE id = ?");
        $stmt->bind_param("ssssssi", $novo_nome, $novo_email, $senha_hash, $novo_telefone, $novo_nivel, $foto_nome, $id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, email = ?, telefone = ?, nivel = ?, foto = ? WHERE id = ?");
        $stmt->bind_param("sssssi", $novo_nome, $novo_email, $novo_telefone, $novo_nivel, $foto_nome, $id);
    }

    if ($stmt->execute()) {
        header("Location: usuarios.php");
        exit;
    } else {
        echo "Erro ao atualizar: " . $stmt->error;
    }

    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar Usuário</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
  <h2><i class="fas fa-user-edit me-2"></i>Editar Usuário</h2>

  <form method="POST" enctype="multipart/form-data" class="mt-4">
    <div class="mb-3">
      <label for="nome" class="form-label">Nome</label>
      <input type="text" name="nome" id="nome" class="form-control" required value="<?= htmlspecialchars($nome) ?>">
    </div>

    <div class="mb-3">
      <label for="email" class="form-label">E-mail</label>
      <input type="email" name="email" id="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
    </div>

    <div class="mb-3">
      <label for="telefone" class="form-label">Telefone</label>
      <input type="text" name="telefone" id="telefone" class="form-control" placeholder="+55 (11) 91234-5678" value="<?= htmlspecialchars($telefone) ?>">
    </div>

    <div class="mb-3">
      <label for="senha" class="form-label">Nova Senha</label>
      <input type="password" name="senha" id="senha" class="form-control" placeholder="Deixe em branco para não alterar">
    </div>

    <?php if ($nivel_logado === 'administrador'): ?>
      <div class="mb-3">
        <label for="nivel" class="form-label">Permissão</label>
        <select name="nivel" id="nivel" class="form-select" required>
          <option value="usuario" <?= $nivel === 'usuario' ? 'selected' : '' ?>>Usuário</option>
          <option value="admin" <?= $nivel === 'administrador' ? 'selected' : '' ?>>Administrador</option>
        </select>
      </div>
    <?php endif; ?>

    <div class="mb-3">
      <label class="form-label">Foto de Perfil</label><br>
      <?php if ($foto): ?>
        <img src="uploads/<?= htmlspecialchars($foto) ?>" width="64" class="rounded mb-2" alt="Foto Atual">
      <?php endif; ?>
      <input type="file" name="foto" class="form-control">
    </div>

    <button type="submit" class="btn btn-success">Salvar Alterações</button>
    <a href="usuarios.php" class="btn btn-secondary">Cancelar</a>
  </form>
</body>
</html>
