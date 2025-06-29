<?php
// Configuração: caminho da pasta onde estão os arquivos PHP do seu projeto
$diretorio = __DIR__; // Usa a pasta atual, mude se quiser

function trocarAdminPorAdministrador($path) {
    $backupPath = $path . '.bak';

    // Lê o conteúdo original
    $conteudo = file_get_contents($path);
    if ($conteudo === false) {
        echo "Erro ao ler o arquivo $path\n";
        return;
    }

    // Verifica se contém 'admin' em comparação com nivel (simplificado)
    // Exemplo: if ($nivel === 'administrador') ou if($usuario['nivel']=='administrador')
    $pattern = '/(\$[a-zA-Z0-9_\[\]\'"]+\s*==={0,1}\s*[\'"])admin([\'"])/';

    if (preg_match($pattern, $conteudo)) {
        // Faz backup do arquivo original
        copy($path, $backupPath);

        // Substitui 'admin' por 'administrador' nas comparações
        $novoConteudo = preg_replace($pattern, '$1administrador$2', $conteudo);

        // Salva arquivo atualizado
        file_put_contents($path, $novoConteudo);

        echo "Arquivo atualizado: $path (backup salvo em $backupPath)\n";
    }
}

function percorrerPasta($dir) {
    $itens = scandir($dir);
    foreach ($itens as $item) {
        if ($item === '.' || $item === '..') continue;
        $caminho = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($caminho)) {
            percorrerPasta($caminho);
        } elseif (is_file($caminho) && pathinfo($caminho, PATHINFO_EXTENSION) === 'php') {
            trocarAdminPorAdministrador($caminho);
        }
    }
}

// Executa a varredura na pasta
echo "Iniciando atualização na pasta: $diretorio\n";
percorrerPasta($diretorio);
echo "Atualização finalizada.\n";
