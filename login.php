<?php
include 'conexao.php';
session_start();

define('MAX_TENTATIVAS', 5);
define('TEMPO_BLOQUEIO_SEGUNDOS', 900); // 15 minutos

if (!isset($_SESSION['tentativas_login'])) {
    $_SESSION['tentativas_login'] = 0;
    $_SESSION['primeira_tentativa'] = time();
}

$mensagem = '';
$tipo = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $tempo_passado = time() - $_SESSION['primeira_tentativa'];

    if ($tempo_passado > TEMPO_BLOQUEIO_SEGUNDOS) {
        $_SESSION['tentativas_login'] = 0;
        $_SESSION['primeira_tentativa'] = time();
    }

    if ($_SESSION['tentativas_login'] >= MAX_TENTATIVAS) {
        $tempo_restante = TEMPO_BLOQUEIO_SEGUNDOS - $tempo_passado;
        $mensagem = "Você excedeu o número máximo de tentativas. Tente novamente em " . ceil($tempo_restante / 60) . " minuto(s).";
        $tipo = "danger";
    } else {
        $email = trim($_POST['email'] ?? '');
        $senha = trim($_POST['senha'] ?? '');

        if (empty($email) || empty($senha)) {
            $mensagem = "Por favor, preencha o e-mail e a senha.";
            $tipo = "warning";
        } else {
            $stmt = $conn->prepare("SELECT id, senha FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $stmt->bind_result($id, $senha_hash);
                $stmt->fetch();

                if (password_verify($senha, $senha_hash)) {
                    $_SESSION['usuario_id'] = $id;
                    $_SESSION['tentativas_login'] = 0;
                    header("Location: index.php");
                    exit;
                }
            }

            $_SESSION['tentativas_login']++;
            $restantes = MAX_TENTATIVAS - $_SESSION['tentativas_login'];
            $msg_extra = $restantes > 0 ? "Você tem mais $restantes tentativa(s)." : "Você excedeu o número máximo de tentativas. Aguarde 15 minutos.";
            $mensagem = "E-mail ou senha incorretos. $msg_extra";
            $tipo = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Login - TCC Kanban</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card p-4 shadow-sm" style="width: 100%; max-width: 380px;">

    <!-- Logo removida -->

    <h2 class="mb-1 text-center">Login</h2>
    <p class="text-center text-muted mb-3" style="font-size: 0.9rem;">Versão: 1.0.00</p>

    <?php if ($mensagem): ?>
      <div class="alert alert-<?= htmlspecialchars($tipo) ?>"><?= htmlspecialchars($mensagem) ?></div>
    <?php endif; ?>

    <form method="POST" novalidate>
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input
          type="email"
          id="email"
          name="email"
          class="form-control"
          placeholder="Digite seu e-mail"
          required
          value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
          <?= ($tipo === 'danger' && $_SESSION['tentativas_login'] >= MAX_TENTATIVAS) ? 'disabled' : '' ?>
        />
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input
          type="password"
          id="senha"
          name="senha"
          class="form-control"
          placeholder="Digite sua senha"
          required
          <?= ($tipo === 'danger' && $_SESSION['tentativas_login'] >= MAX_TENTATIVAS) ? 'disabled' : '' ?>
        />
      </div>
      <button
        type="submit"
        class="btn btn-primary w-100"
        <?= ($tipo === 'danger' && $_SESSION['tentativas_login'] >= MAX_TENTATIVAS) ? 'disabled' : '' ?>
      >Entrar</button>
    </form>

    <div class="text-center my-3">
      <span>ou</span>
    </div>

    <a href="login_google.php" class="btn btn-outline-danger w-100 mb-3">
      <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" style="width:20px; margin-right:8px;">
      Entrar com Google
    </a>

    <p class="mt-3 text-center">
      Não tem conta? <a href="registro.php">Registre-se</a>
    </p>

    <!-- Rodapé -->
    <p class="text-center text-muted mt-4" style="font-size: 0.8rem;">
      © 2025 Felipe Costa Correa - F2Code Systems. Todos os direitos reservados.
    </p>
  </div>
</body>
</html>
