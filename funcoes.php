<?php
// Define timezone padrão
date_default_timezone_set('America/Sao_Paulo');

/**
 * Retorna uma saudação com base na hora atual.
 * Exemplo: Bom dia, Boa tarde, Boa noite
 */
function saudacao()
{
    $hora = date('H');
    if ($hora < 12) {
        return "Bom dia";
    } elseif ($hora < 18) {
        return "Boa tarde";
    } else {
        return "Boa noite";
    }
}

/**
 * Escapa uma string de forma segura para saída HTML
 */
function escape($valor)
{
    return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
}

/**
 * Verifica se o usuário está logado e redireciona se não estiver.
 */
function verificar_login()
{
    session_start();
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: login.php");
        exit;
    }
}

/**
 * Formata datas no padrão brasileiro (d/m/Y H:i)
 */
function formatar_data($data)
{
    if (!$data || $data === '0000-00-00 00:00:00') return '-';
    return date('d/m/Y H:i', strtotime($data));
}

/**
 * Retorna o nome e demais dados do usuário logado
 */
function obter_dados_usuario($conn, $usuario_id)
{
    $stmt = $conn->prepare("SELECT nome, foto, nivel FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuario_id);
    $stmt->execute();
    $stmt->bind_result($nome, $foto, $nivel);
    $stmt->fetch();
    $stmt->close();

    return [
        'nome' => $nome,
        'foto' => $foto,
        'nivel' => $nivel
    ];
}
