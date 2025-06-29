<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

// Obter nível do usuário logado
$stmt = $conn->prepare("SELECT nivel FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nivel_usuario);
$stmt->fetch();
$stmt->close();

// Normalizar o nível para evitar erros de comparação
$nivel_usuario = strtolower(trim($nivel_usuario));

$status_filtro = $_GET['status'] ?? '';
$usuario_filtro = $_GET['usuario'] ?? '';

$query = "SELECT t.id, t.titulo, t.descricao, t.status, t.prioridade, t.prazo_inicial, t.prazo_final, 
                 c.nome AS categoria, u.nome AS usuario_nome
          FROM tarefas t
          LEFT JOIN categorias c ON t.categoria_id = c.id
          INNER JOIN usuarios u ON t.usuario_id = u.id
          WHERE 1=1";

$params = [];
$types = '';

// Filtro de status
if (!empty($status_filtro)) {
    $query .= " AND t.status = ?";
    $params[] = $status_filtro;
    $types .= 's';
}

// Filtro por usuário (apenas se admin)
if ($nivel_usuario === 'administrador') {
    if (!empty($usuario_filtro) && ctype_digit($usuario_filtro)) {
        $query .= " AND t.usuario_id = ?";
        $params[] = (int)$usuario_filtro;
        $types .= 'i';
    }
} else {
    // Usuário comum vê apenas suas tarefas
    $query .= " AND t.usuario_id = ?";
    $params[] = $usuario_id;
    $types .= 'i';
}

$query .= " ORDER BY t.id DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

function traduzStatus($status) {
    return match ($status) {
        'todo' => 'A Fazer',
        'inprogress' => 'Em Progresso',
        'done' => 'Concluído',
        default => ucfirst($status)
    };
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Tarefas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
    <h1 class="mb-4">Tarefas</h1>

    <!-- Filtros -->
    <form method="GET" class="row g-3 mb-4 align-items-end">
        <div class="col-auto">
            <label class="form-label">Status</label>
            <select name="status" class="form-select" onchange="this.form.submit()">
                <option value="">-- Filtrar por Status --</option>
                <option value="todo" <?= $status_filtro == 'todo' ? 'selected' : '' ?>>A Fazer</option>
                <option value="inprogress" <?= $status_filtro == 'inprogress' ? 'selected' : '' ?>>Em Progresso</option>
                <option value="done" <?= $status_filtro == 'done' ? 'selected' : '' ?>>Concluído</option>
            </select>
        </div>

        <?php if ($nivel_usuario === 'administrador'): ?>
            <div class="col-auto">
                <label class="form-label">Usuário</label>
                <select name="usuario" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Todos os Usuários --</option>
                    <?php
                    $usuarios_result = $conn->query("SELECT id, nome FROM usuarios ORDER BY nome ASC");
                    while ($u = $usuarios_result->fetch_assoc()):
                    ?>
                        <option value="<?= $u['id'] ?>" <?= $usuario_filtro == $u['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($u['nome']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        <?php endif; ?>

        <div class="col-auto">
            <a href="tarefas.php" class="btn btn-secondary">Limpar Filtro</a>
        </div>
    </form>

    <!-- Tabela de Tarefas -->
    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Título</th>
                <th>Descrição</th>
                <th>Categoria</th>
                <th>Prazo Inicial</th>
                <th>Prazo Final</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th>Usuário</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($t = $result->fetch_assoc()): ?>
                <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['titulo']) ?></td>
                    <td><?= nl2br(htmlspecialchars($t['descricao'])) ?></td>
                    <td><?= htmlspecialchars($t['categoria'] ?? 'Sem categoria') ?></td>
                    <td><?= $t['prazo_inicial'] ? date('d/m/Y', strtotime($t['prazo_inicial'])) : '-' ?></td>
                    <td><?= $t['prazo_final'] ? date('d/m/Y', strtotime($t['prazo_final'])) : '-' ?></td>
                    <td><?= ucfirst(htmlspecialchars($t['prioridade'])) ?></td>
                    <td><?= traduzStatus($t['status']) ?></td>
                    <td><?= htmlspecialchars($t['usuario_nome']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <a href="index.php" class="btn btn-secondary">Voltar</a>
</body>
</html>
