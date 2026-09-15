<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$id = trim($_POST['id'] ?? '');
if ($id === '') {
    http_response_code(422);
    echo json_encode(['error' => 'ID wajib diisi.']);
    exit;
}

$db = getDB();
$stmt = $db->prepare("DELETE FROM tickets WHERE id = :id");
$stmt->execute([':id' => $id]);

if ($stmt->rowCount() === 0) {
    http_response_code(404);
    echo json_encode(['error' => 'Ticket tidak ditemukan.']);
    exit;
}

echo json_encode(['success' => true]);
