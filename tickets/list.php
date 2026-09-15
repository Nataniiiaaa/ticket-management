<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../lib/customer_client.php';

try {
    $db = getDB();
    $tickets = $db->query("SELECT * FROM tickets ORDER BY id DESC")->fetchAll();

    // Ambil data customer dari API untuk setiap ticket
    $customerResults = fetchCustomers();
    $customers = [];

    if ($customerResults['ok']) {
        $list = $customerResults['data']['data'] ?? $customerResults['data'];
        foreach ($list as $c) {
            $customerMap[$c['id']] = $c['name'] ?? '(Nama Tidak Tersedia)';
        }
    }
    
    foreach ($tickets as &$t) {
        $t['customer_name'] = $customerMap[$t['customer_id']] ?? '(customer tidak ditemukan)';
    }

    echo json_encode([
        'tickets' => $tickets,
        // dikirim ke frontend biar bisa dikasih tau "nama customer mungkin ga akurat"
        'customer_api_warning' => $customerResult['ok'] ? null : $customerResult['error'],
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data ticket.']);
}