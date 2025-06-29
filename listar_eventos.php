<?php
session_start();
require_once 'google_client.php';

$client = getGoogleClient();
if (!$client) {
    header('Location: login_google.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Eventos do Google Calendar</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4">
<div class="container">
    <h2>Eventos do Google Calendar</h2>
    <div id="eventos" class="mt-4">
        <p>Carregando eventos...</p>
    </div>
</div>

<script>
function carregarEventos() {
    fetch('api_eventos.php')
        .then(response => response.json())
        .then(data => {
            const container = document.getElementById('eventos');

            if (!data.length) {
                container.innerHTML = '<p>Nenhum evento encontrado.</p>';
                return;
            }

            let html = '<ul class="list-group">';
            data.forEach(evento => {
                const inicio = new Date(evento.start);
                const fim = new Date(evento.end);
                const resumo = evento.summary ?? 'Sem título';
                const descricao = evento.description ?? '';

                html += `<li class="list-group-item">
                    <strong>${resumo}</strong><br>
                    Início: ${isNaN(inicio) ? 'Data inválida' : inicio.toLocaleString()}<br>
                    Fim: ${isNaN(fim) ? 'Data inválida' : fim.toLocaleString()}<br>
                    ${descricao}
                </li>`;
            });
            html += '</ul>';
            container.innerHTML = html;
        })
        .catch(err => {
            document.getElementById('eventos').innerHTML = '<p>Erro ao carregar eventos.</p>';
            console.error(err);
        });
}

// Carrega automaticamente e a cada 10 segundos
carregarEventos();
setInterval(carregarEventos, 10000);
</script>
</body>
</html>
