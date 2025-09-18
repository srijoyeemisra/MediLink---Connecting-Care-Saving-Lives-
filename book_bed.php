<?php
// book_bed.php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

// Expect POST with id and type ('general' or 'icu')
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id = isset($data['id']) ? intval($data['id']) : 0;
$type = isset($data['type']) ? strtolower(trim($data['type'])) : '';

if ($id <= 0 || !in_array($type, ['general', 'icu'])) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Invalid parameters']);
    exit;
}

$col = ($type === 'general') ? 'general_beds' : 'icu_beds';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    // Start transaction
    $pdo->beginTransaction();

    // Lock row
    $stmt = $pdo->prepare("SELECT general_beds, icu_beds FROM hospital_master WHERE id = :id FOR UPDATE");
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'Hospital not found']);
        exit;
    }

    $current = intval($row[$col]);
    if ($current <= 0) {
        $pdo->rollBack();
        echo json_encode(['status'=>'error','message'=>"No {$type} beds available"]);
        exit;
    }

    // Decrement
    $stmt = $pdo->prepare("UPDATE hospital_master SET {$col} = {$col} - 1 WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Fetch updated counts
    $stmt = $pdo->prepare("SELECT general_beds, icu_beds FROM hospital_master WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);

    $pdo->commit();

    // Return updated counts
    echo json_encode([
        'status' => 'ok',
        'message' => ucfirst($type) . ' bed booked successfully',
        'hospital' => [
            'id' => $id,
            'general_beds' => intval($updated['general_beds']),
            'icu_beds' => intval($updated['icu_beds'])
        ]
    ]);
} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database error', 'details'=>$e->getMessage()]);
}
