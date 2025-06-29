<?php
session_start();
require_once 'google_client.php';

header('Content-Type: application/json');

// Verifica se o usuário está autenticado e se o token é válido
$client = getGoogleClient();

if (!$client) {
    http_response_code(401);
    echo json_encode(['erro' => 'Não autenticado ou token expirado.']);
    exit;
}

try {
    $service = new Google_Service_Calendar($client);

    $calendarId = 'primary';
    $optParams = [
        'maxResults' => 10,
        'orderBy' => 'startTime',
        'singleEvents' => true,
        'timeMin' => date('c'),
    ];

    $results = $service->events->listEvents($calendarId, $optParams);
    $eventos = [];

    foreach ($results->getItems() as $event) {
        $eventos[] = [
            'summary' => $event->getSummary() ?? 'Sem título',
            'description' => $event->getDescription() ?? '',
            'start' => $event->getStart()->getDateTime() ?? $event->getStart()->getDate(),
            'end' => $event->getEnd()->getDateTime() ?? $event->getEnd()->getDate(),
        ];
    }

    echo json_encode($eventos);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['erro' => 'Erro ao buscar eventos: ' . $e->getMessage()]);
}
