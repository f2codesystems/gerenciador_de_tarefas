<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';
$usuario_id = $_SESSION['usuario_id'];

$hoje = date('Y-m-d');
$data_limite = date('Y-m-d', strtotime('+3 days'));

$tipos_status = [
    'all' => 'Todos',
    'vencido' => 'Vencidos',
    'proximo' => 'Próximos',
    'done' => 'Concluídos'
];

$filtro_tipo = $_GET['tipo'] ?? 'all';

$query = "
  SELECT t.id, t.titulo, t.descricao, t.prioridade, t.status, t.prazo_inicial, t.prazo_final, c.nome AS categoria
  FROM tarefas t
  LEFT JOIN categorias c ON t.categoria_id = c.id
  WHERE t.usuario_id = ?
";

$params = [$usuario_id];
$types = "i";

if ($filtro_tipo === 'vencido') {
    $query .= " AND t.status != 'done' AND t.prazo_final < ?";
    $params[] = $hoje;
    $types .= "s";
} elseif ($filtro_tipo === 'proximo') {
    $query .= " AND t.status != 'done' AND t.prazo_final BETWEEN ? AND ?";
    $params[] = $hoje;
    $params[] = $data_limite;
    $types .= "ss";
} elseif ($filtro_tipo === 'done') {
    $query .= " AND t.status = 'done'";
}

$query .= " ORDER BY t.prazo_final ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$notificacoes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
  <meta charset="UTF-8" />
  <title>Notificações</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet" />
</head>
<body class="p-4">
  <div class="container">
    <h2>Notificações de Tarefas</h2>
    <a href="index.php" class="btn btn-secondary mb-3">Voltar</a>

    <form method="GET" class="mb-4 d-flex align-items-center gap-2">
      <label for="tipo" class="form-label mb-0">Filtrar por:</label>
      <select name="tipo" id="tipo" class="form-select w-auto">
        <?php foreach ($tipos_status as $key => $label): ?>
          <option value="<?= $key ?>" <?= ($filtro_tipo === $key) ? 'selected' : '' ?>><?= $label ?></option>
        <?php endforeach; ?>
      </select>
      <button type="submit" class="btn btn-primary">Filtrar</button>
    </form>

    <?php if (count($notificacoes) === 0): ?>
      <div class="alert alert-info">Não há notificações no momento.</div>
    <?php else: ?>
      <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
          <tr>
            <th>ID</th>
            <th>Título</th>
            <th>Descrição</th>
            <th>Prioridade</th>
            <th>Status</th>
            <th>Prazo Inicial</th>
            <th>Prazo Final</th>
            <th>Categoria</th>
            <th>Etiqueta</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($notificacoes as $t): ?>
            <?php
              if ($t['status'] === 'done') {
                $icone = '<i class="fas fa-check-circle text-success" title="Concluído"></i>';
                $badge = '<span class="badge bg-success">Concluído</span>';
              } elseif ($t['prazo_final'] < $hoje) {
                $icone = '<i class="fas fa-exclamation-circle text-danger" title="Vencido"></i>';
                $badge = '<span class="badge bg-danger">Vencido</span>';
              } else {
                $icone = '<i class="fas fa-clock text-warning" title="Próximo"></i>';
                $badge = '<span class="badge bg-warning text-dark">Próximo</span>';
              }
            ?>
            <tr>
              <td class="text-center"><?= $t['id'] ?></td>
              <td><?= htmlspecialchars($t['titulo']) ?> <?= $icone ?></td>
              <td><?= nl2br(htmlspecialchars($t['descricao'])) ?></td>
              <td><?= htmlspecialchars($t['prioridade']) ?></td>
              <td><?= traduzStatus($t['status']) ?></td>
              <td><?= $t['prazo_inicial'] ? date('d/m/Y', strtotime($t['prazo_inicial'])) : '-' ?></td>
              <td><?= $t['prazo_final'] ? date('d/m/Y', strtotime($t['prazo_final'])) : '-' ?></td>
              <td><?= htmlspecialchars($t['categoria'] ?? 'Sem categoria') ?></td>
              <td class="text-center"><?= $badge ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>
