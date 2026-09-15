<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config.php';

$db = getDB();

// GET untuk ambil data ticket yang mau diedit
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? '';
    $stmt = $db->prepare("SELECT * FROM tickets WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $ticket = $stmt->fetch();

    if (!$ticket) {
        http_response_code(404);
        echo json_encode(['error' => 'Ticket tidak ditemukan.']);
        exit;
    }

    echo json_encode($ticket);
    exit;
}

// POST untuk update data tiket
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = trim($_POST['id'] ?? '');
    $customerId  = trim($_POST['customer_id'] ?? '');
    $subject     = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $priority    = trim($_POST['priority'] ?? '');
    $status      = trim($_POST['status'] ?? '');

    $errors = [];
    if ($id === '')         $errors[] = 'ID ticket tidak valid.';
    if ($customerId === '') $errors[] = 'Customer wajib dipilih.';
    if ($subject === '')    $errors[] = 'Subject wajib diisi.';
    if (!in_array($priority, ['low', 'medium', 'high'], true)) $errors[] = 'Priority tidak valid.';
    if (!in_array($status, ['open', 'progress', 'closed'], true)) $errors[] = 'Status tidak valid.';

    if (!empty($errors)) {
        http_response_code(422);
        echo json_encode(['error' => implode(' ', $errors)]);
        exit;
    }

    $stmt = $db->prepare(
        "UPDATE tickets SET customer_id = :customer_id, subject = :subject, description = :description, priority = :priority, status = :status
         WHERE id = :id"
    );
    $stmt->execute([
        ':customer_id' => $customerId,
        ':subject'     => $subject,
        ':description' => $description,
        ':priority'    => $priority,
        ':status'      => $status,
        ':id'          => $id,
    ]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        echo json_encode(['error' => 'Ticket tidak ditemukan atau tidak ada perubahan.']);
        exit;
    }

    echo json_encode(['success' => true]);
    exit;
}

http_response_code(405);
echo json_encode(['error' => 'Method not allowed']);