<?php
// get_doctors.php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

$hospital_id = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;
if ($hospital_id <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'hospital_id required']);
    exit;
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("SELECT id, name, specialization, bio, phone FROM doctors WHERE hospital_id = :hid ORDER BY specialization, name");
    $stmt->execute([':hid'=>$hospital_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['status'=>'ok','doctors'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database error', 'details'=>$e->getMessage()]);
}
