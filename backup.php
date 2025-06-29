<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

// 🔒 Verifica se o usuário é administrador (alterado para 'administrador')
$usuario_id = $_SESSION['usuario_id'];
$stmt = $conn->prepare("SELECT nivel FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$stmt->bind_result($nivel);
$stmt->fetch();
$stmt->close();

if ($nivel !== 'administrador') {  // <- aqui a alteração
    echo "Acesso restrito. Esta funcionalidade está disponível apenas para administradores.";
    exit;
}

// Nome do arquivo de backup
$nomeArquivo = "backup_kanban_" . date("Y-m-d_H-i-s") . ".sql";

// Cabeçalhos para forçar o download
header("Content-Type: application/octet-stream");
header("Content-Disposition: attachment; filename=\"$nomeArquivo\"");
header("Pragma: no-cache");
header("Expires: 0");

// Configurações de banco
$host = 'localhost';
$usuario = 'root';     // ajuste se necessário
$senha = '';           // ajuste se necessário
$banco = 'kanban';     // ajuste para o nome do seu banco

// Caminho completo do mysqldump no Mac com XAMPP
$mysqldumpPath = "/Applications/XAMPP/xamppfiles/bin/mysqldump";

// Monta o comando completo
$comando = "$mysqldumpPath --host=$host --user=$usuario --password=$senha $banco 2>&1";

// Executa e envia o conteúdo do backup
passthru($comando);
exit;
?>
