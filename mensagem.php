<?php
// Valida tipo para evitar CSS quebrado
$tipos_validos = ['success', 'danger', 'info'];
if (!isset($tipo) || !in_array($tipo, $tipos_validos)) {
    $tipo = 'info';
}

$mensagem = isset($mensagem) ? $mensagem : "Ação executada.";
$link = isset($link) ? $link : "index.php";
$link_texto = isset($link_texto) ? $link_texto : "Voltar ao Kanban";

// Define um tempo padrão de redirecionamento se não informado (exemplo: 5 segundos)
// Se quiser que não redirecione automaticamente, defina $tempo_redirecionamento = 0 ou null no script que inclui essa página
if (!isset($tempo_redirecionamento) || !$tempo_redirecionamento) {
    $tempo_redirecionamento = null; // sem redirecionamento automático
} else {
    $tempo_redirecionamento = intval($tempo_redirecionamento);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Mensagem - TCC Kanban</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <?php if ($tempo_redirecionamento): ?>
    <meta http-equiv="refresh" content="<?php echo $tempo_redirecionamento; ?>;url=<?php echo htmlspecialchars($link); ?>">
  <?php endif; ?>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <style>
    body {
      background-color: #f8f9fa;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .message-container {
      max-width: 480px;
      margin: 100px auto;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      background: #fff;
      text-align: center;
    }
    .alert-custom {
      padding: 15px 20px;
      border-radius: 8px;
      font-size: 18px;
      font-weight: 500;
      margin-bottom: 20px;
    }
    .alert-success {
      background-color: #d1e7dd;
      color: #0f5132;
      border: 1px solid #badbcc;
    }
    .alert-danger {
      background-color: #f8d7da;
      color: #842029;
      border: 1px solid #f5c2c7;
    }
    .alert-info {
      background-color: #cff4fc;
      color: #055160;
      border: 1px solid #b6effb;
    }
    .btn-redirect {
      display: inline-block;
      padding: 10px 20px;
      border-radius: 6px;
      text-decoration: none;
      font-weight: bold;
      transition: background 0.3s;
    }
    .btn-success {
      background-color: #198754;
      color: white;
    }
    .btn-success:hover {
      background-color: #157347;
    }
    .btn-danger {
      background-color: #dc3545;
      color: white;
    }
    .btn-danger:hover {
      background-color: #bb2d3b;
    }
    .btn-info {
      background-color: #0dcaf0;
      color: white;
    }
    .btn-info:hover {
      background-color: #31d2f2;
    }
  </style>
</head>
<body>
  <div class="message-container">
    <div class="alert-custom alert-<?php echo htmlspecialchars($tipo); ?>">
      <?php echo htmlspecialchars($mensagem); ?>
    </div>
    <a href="<?php echo htmlspecialchars($link); ?>" class="btn-redirect btn-<?php echo htmlspecialchars($tipo); ?>">
      <?php echo htmlspecialchars($link_texto); ?>
    </a>
  </div>
</body>
</html>
