<?php
// get_doctor_schedule.php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
if ($doctor_id <= 0) {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'doctor_id required']);
    exit;
}

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("SELECT id, date, start_time, end_time, slots_total, slots_available FROM doctor_schedule WHERE doctor_id = :doc AND date >= CURDATE() ORDER BY date ASC");
    $stmt->execute([':doc'=>$doctor_id]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as &$r) {
      $r['slots_total'] = intval($r['slots_total']);
      $r['slots_available'] = intval($r['slots_available']);
      $r['date'] = $r['date'];
      $r['start_time'] = $r['start_time'];
      $r['end_time'] = $r['end_time'];
    }

    echo json_encode(['status'=>'ok','schedule'=>$rows], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database error','details'=>$e->getMessage()]);
}
