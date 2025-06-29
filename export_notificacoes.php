<?php
include 'funcoes.php';
verificar_login();
include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$hoje = date('Y-m-d');
$data_limite = date('Y-m-d', strtotime('+3 days'));

$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim = $_POST['data_fim'] ?? '';

$query = "
  SELECT t.titulo, t.descricao, t.prioridade, t.status, t.prazo_inicial, t.prazo_final, c.nome AS categoria, u.nome AS usuario_nome
  FROM tarefas t
  LEFT JOIN categorias c ON t.categoria_id = c.id
  INNER JOIN usuarios u ON t.usuario_id = u.id
  WHERE t.usuario_id = ?
";
$params = [$usuario_id];
$types = "i";

if ($data_inicio) {
    $query .= " AND t.prazo_final >= ?";
    $params[] = $data_inicio;
    $types .= "s";
}
if ($data_fim) {
    $query .= " AND t.prazo_final <= ?";
    $params[] = $data_fim;
    $types .= "s";
}

$query .= " ORDER BY t.prazo_final ASC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
$notificacoes = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=relatorio_notificacoes.csv');

$output = fopen('php://output', 'w');
fputcsv($output, ['Título', 'Descrição', 'Prioridade', 'Status da Tarefa', 'Situação do Prazo', 'Prazo Inicial', 'Prazo Final', 'Categoria', 'Usuário']);

foreach ($notificacoes as $t) {
    if ($t['status'] === 'done') {
        $situacao = 'Concluído';
    } elseif ($t['prazo_final'] < $hoje) {
        $situacao = 'Vencido';
    } else {
        $situacao = 'Próximo';
    }

    fputcsv($output, [
        $t['titulo'],
        $t['descricao'],
        ucfirst($t['prioridade']),
        ucfirst($t['status']),
        $situacao,
        $t['prazo_inicial'],
        $t['prazo_final'],
        $t['categoria'] ?? 'Sem categoria',
        $t['usuario_nome']
    ]);
}

fclose($output);
exit;
?>
