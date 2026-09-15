<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

// --- Validasi SERVER-SIDE 
$customerId = trim($_POST['customer_id'] ?? '');
$subject    = trim($_POST['subject'] ?? '');
$priority   = trim($_POST['priority'] ?? '');
$status     = trim($_POST['status'] ?? '');

$errors = [];
if ($customerId === '') $errors[] = 'Customer wajib dipilih.';
if ($subject === '')    $errors[] = 'Subject wajib diisi.';
if (!in_array($priority, ['low', 'medium', 'high'], true)) $errors[] = 'Priority tidak valid.';
if (!in_array($status, ['open', 'progress', 'closed'], true)) $errors[] = 'Status tidak valid.';

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['error' => implode(' ', $errors)]);
    exit;
}

try {
    $db = getDB();
    $stmt = $db->prepare(
        "INSERT INTO tickets (customer_id, subject, priority, status)
         VALUES (:customer_id, :subject, :priority, :status)"
    );
    $stmt->execute([
        ':customer_id' => $customerId,
        ':subject'     => $subject,
        ':priority'    => $priority,
        ':status'      => $status,
    ]);

    echo json_encode(['success' => true, 'id' => $db->lastInsertId()]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal menyimpan ticket.']);
}
