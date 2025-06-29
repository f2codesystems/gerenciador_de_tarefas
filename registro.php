<?php
include 'conexao.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $confirmar_senha = trim($_POST['confirmar_senha'] ?? '');
    $telefone = trim($_POST['telefone'] ?? '');

    if (empty($nome) || empty($email) || empty($senha) || empty($confirmar_senha) || empty($telefone)) {
        $tipo = "danger";
        $mensagem = "Todos os campos são obrigatórios.";
        $link = "registro.php";
        $link_texto = "Voltar";
        include 'mensagem.php';
        exit;
    }

    if ($senha !== $confirmar_senha) {
        $tipo = "danger";
        $mensagem = "As senhas não coincidem.";
        $link = "registro.php";
        $link_texto = "Tentar novamente";
        include 'mensagem.php';
        exit;
    }

    if (!preg_match('/^\+\d{1,3}\s?\d{6,15}$/', $telefone)) {
        $tipo = "danger";
        $mensagem = "Telefone inválido. Use o formato internacional: +55 11912345678";
        $link = "registro.php";
        $link_texto = "Corrigir telefone";
        include 'mensagem.php';
        exit;
    }

    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO usuarios (nome, email, senha, telefone) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nome, $email, $senha_hash, $telefone);

    if ($stmt->execute()) {
        $tipo = "success";
        $mensagem = "Usuário registrado com sucesso! Agora faça login.";
        $link = "login.php";
        $link_texto = "Ir para login";
        include 'mensagem.php';
        exit;
    } else {
        $tipo = "danger";
        $mensagem = "Erro ao registrar: " . $stmt->error;
        $link = "registro.php";
        $link_texto = "Tentar novamente";
        include 'mensagem.php';
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Registro - TCC Kanban</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.css" />
  <style>
    .iti { width: 100%; }
  </style>
</head>
<body class="d-flex justify-content-center align-items-center vh-100 bg-light">
  <div class="card p-4 shadow-sm" style="width: 100%; max-width: 400px;">
    <h2 class="mb-4 text-center">Registro</h2>
    <form method="POST" id="registroForm" novalidate>
      <div class="mb-3">
        <label for="nome" class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" placeholder="Nome" required>
      </div>
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <input type="email" name="email" class="form-control" placeholder="E-mail" required>
      </div>
      <div class="mb-3">
        <label for="telefone" class="form-label">Telefone</label>
        <input type="tel" name="telefone" id="telefone" class="form-control" required>
      </div>
      <div class="mb-3">
        <label for="senha" class="form-label">Senha</label>
        <input type="password" name="senha" id="senha" class="form-control" placeholder="Senha" required>
      </div>
      <div class="mb-3">
        <label for="confirmar_senha" class="form-label">Confirmar Senha</label>
        <input type="password" name="confirmar_senha" id="confirmar_senha" class="form-control" placeholder="Repita a senha" required>
      </div>
      <button type="submit" class="btn btn-success w-100">Registrar</button>
    </form>
    <p class="mt-3 text-center">Já tem conta? <a href="login.php">Fazer login</a></p>
  </div>

  <!-- Scripts -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
  <script>
    const input = document.querySelector("#telefone");
    const iti = window.intlTelInput(input, {
      initialCountry: "br",
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js",
    });

    // Formatação e inserção correta no POST
    const form = document.querySelector("#registroForm");
    form.addEventListener("submit", function (e) {
      const senha = document.getElementById("senha").value;
      const confirmar = document.getElementById("confirmar_senha").value;

      if (senha !== confirmar) {
        e.preventDefault();
        alert("As senhas não coincidem.");
        return false;
      }

      // Substitui valor do telefone no POST com o formato completo internacional
      input.value = iti.getNumber();
    });
  </script>
</body>
</html>
