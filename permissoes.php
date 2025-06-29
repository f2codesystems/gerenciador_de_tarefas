<?php
session_start();
include 'conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Verifica se é administrador (corrigido de 'admin' para 'administrador')
$stmt = $conn->prepare("SELECT nivel FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['usuario_id']);
$stmt->execute();
$stmt->bind_result($nivel_usuario);
$stmt->fetch();
$stmt->close();

if ($nivel_usuario !== 'administrador') {  // <- aqui a alteração
    echo "<h3 style='color: red;'>Acesso negado. Esta página é restrita a administradores.</h3>";
    exit;
}

// Atualiza permissão se enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'], $_POST['nivel'])) {
    $usuario_id_alvo = intval($_POST['usuario_id']);

    // Atualizei para aceitar os níveis corretos 'administrador' e 'usuario'
    $novo_nivel = in_array($_POST['nivel'], ['administrador', 'usuario']) ? $_POST['nivel'] : 'usuario';

    $stmt = $conn->prepare("UPDATE usuarios SET nivel = ? WHERE id = ?");
    $stmt->bind_param("si", $novo_nivel, $usuario_id_alvo);
    $stmt->execute();
    $stmt->close();
}

// Lista todos os usuários
$usuarios = $conn->query("SELECT id, nome, email, nivel FROM usuarios ORDER BY nome");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Permissões de Usuário</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <h2>Gerenciar Permissões</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Email</th>
                <th>Nível</th>
                <th>Ação</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($u = $usuarios->fetch_assoc()): ?>
                <tr>
                    <form method="POST">
                        <input type="hidden" name="usuario_id" value="<?= $u['id'] ?>">
                        <td><?= htmlspecialchars($u['nome']) ?></td>
                        <td><?= htmlspecialchars($u['email']) ?></td>
                        <td>
                            <select name="nivel" class="form-select">
                                <option value="usuario" <?= $u['nivel'] === 'usuario' ? 'selected' : '' ?>>Usuário</option>
                                <option value="administrador" <?= $u['nivel'] === 'administrador' ? 'selected' : '' ?>>Administrador</option>  <!-- Corrigido aqui -->
                            </select>
                        </td>
                        <td><button type="submit" class="btn btn-primary btn-sm">Salvar</button></td>
                    </form>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <a href="configuracoes.php" class="btn btn-secondary mt-3">Voltar</a>
</body>
</html>
