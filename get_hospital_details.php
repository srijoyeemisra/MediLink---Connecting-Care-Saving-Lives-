<?php
// get_hospital_details.php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'id parameter required']);
    exit;
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $sql = "SELECT id, name, address, phone, latitude, longitude, general_beds, icu_beds FROM hospital_master WHERE id = :id LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':id' => $id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'Hospital not found']);
        exit;
    }

    // cast ints
    $row['general_beds'] = intval($row['general_beds']);
    $row['icu_beds'] = intval($row['icu_beds']);

    echo json_encode(['status'=>'ok','hospital'=>$row], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database error', 'details'=>$e->getMessage()]);
}
