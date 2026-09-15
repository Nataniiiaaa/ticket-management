<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../lib/customer_client.php';

$allowedParams = ['status', 'search'];
$params = [];
foreach ($allowedParams as $p) {
    if (isset($_GET[$p]) && $_GET[$p] !== '') {
        $params[$p] = $_GET[$p];
    }
}

$result = fetchCustomers($params);

if (!$result['ok']) {
    http_response_code($result['http_code'] ?: 502);
    echo json_encode(['error' => $result['error']]);
    exit;
}

echo json_encode($result['data']);