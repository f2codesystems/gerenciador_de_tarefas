<?php
include 'funcoes.php';
verificar_login();
include 'conexao.php';

$usuario_id = $_SESSION['usuario_id'];

$data_inicio = $_POST['data_inicio'] ?? '';
$data_fim = $_POST['data_fim'] ?? '';
$status = $_POST['status'] ?? '';
$formato = $_POST['formato'] ?? 'excel';

$query = "
  SELECT t.id, t.titulo, t.descricao, c.nome AS categoria, t.prazo_inicial, t.prazo_final,
         t.prioridade, t.status, u.nome AS usuario_nome
  FROM tarefas t
  LEFT JOIN categorias c ON t.categoria_id = c.id
  INNER JOIN usuarios u ON t.usuario_id = u.id
  WHERE 1=1
";

$params = [];
$types = "";

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

if ($status) {
    $query .= " AND t.status = ?";
    $params[] = $status;
    $types .= "s";
}

$stmt = $conn->prepare($query);

if ($params) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

$dados = $result->fetch_all(MYSQLI_ASSOC);

$stmt->close();

if ($formato === 'excel') {
    // Exportar CSV
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=relatorio_tarefas.csv');

    $output = fopen('php://output', 'w');
    fputcsv($output, ['ID', 'Título', 'Descrição', 'Categoria', 'Prazo Inicial', 'Prazo Final', 'Prioridade', 'Status', 'Usuário']);

    foreach ($dados as $row) {
        fputcsv($output, [
            $row['id'],
            $row['titulo'],
            $row['descricao'],
            $row['categoria'] ?? 'Sem categoria',
            $row['prazo_inicial'] ?? '',
            $row['prazo_final'] ?? '',
            ucfirst($row['prioridade']),
            ucfirst($row['status']),
            $row['usuario_nome']
        ]);
    }
    fclose($output);
} else {
    // Exportar PDF (se quiser manter PDF)
    // Aqui pode continuar seu código para exportação PDF, se necessário
}

exit;
?>
