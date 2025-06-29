<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';
$usuario_id = $_SESSION['usuario_id'];

if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    $extensao = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $nome_arquivo = uniqid() . '.' . $extensao;
    $caminho = "uploads/$nome_arquivo";

    if (move_uploaded_file($_FILES['foto']['tmp_name'], $caminho)) {
        $stmt = $conn->prepare("UPDATE usuarios SET foto = ? WHERE id = ?");
        $stmt->bind_param("si", $nome_arquivo, $usuario_id);
        $stmt->execute();
        $stmt->close();
        header("Location: perfil.php?ok=1");
        exit;
    } else {
        echo "Erro ao salvar o arquivo.";
    }
} else {
    echo "Nenhuma foto enviada ou erro no upload.";
}
?>
