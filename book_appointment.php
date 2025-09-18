<?php
// book_appointment.php
header('Content-Type: application/json; charset=utf-8');
$config = require __DIR__ . '/config.php';

// Accept JSON body
$input = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$doctor_schedule_id = isset($input['doctor_schedule_id']) ? intval($input['doctor_schedule_id']) : 0;
$doctor_id = isset($input['doctor_id']) ? intval($input['doctor_id']) : 0;
$hospital_id = isset($input['hospital_id']) ? intval($input['hospital_id']) : 0;
$user_name = isset($input['user_name']) ? trim($input['user_name']) : '';
$user_phone = isset($input['user_phone']) ? trim($input['user_phone']) : '';
$user_email = isset($input['user_email']) ? trim($input['user_email']) : '';

if ($doctor_schedule_id <= 0 || $doctor_id <= 0 || $hospital_id <= 0 || $user_name === '' || $user_phone === '') {
    http_response_code(400);
    echo json_encode(['status'=>'error','message'=>'Missing parameters (doctor_schedule_id, doctor_id, hospital_id, user_name, user_phone required)']);
    exit;
}

$session_id = session_id();
if (!$session_id) {
    // start session to capture or create one
    session_start();
    $session_id = session_id();
}
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

    // begin transaction
    $pdo->beginTransaction();

    // lock the row
    $stmt = $pdo->prepare("SELECT id, slots_available, slots_total FROM doctor_schedule WHERE id = :sid FOR UPDATE");
    $stmt->execute([':sid'=>$doctor_schedule_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(['status'=>'error','message'=>'Schedule not found']);
        exit;
    }

    $available = intval($row['slots_available']);
    if ($available <= 0) {
        $pdo->rollBack();
        echo json_encode(['status'=>'error','message'=>'No appointments available for selected date']);
        exit;
    }

    // decrement
    $stmt = $pdo->prepare("UPDATE doctor_schedule SET slots_available = slots_available - 1 WHERE id = :sid");
    $stmt->execute([':sid'=>$doctor_schedule_id]);

    // insert appointment record
    $stmt = $pdo->prepare("INSERT INTO appointments (doctor_schedule_id, doctor_id, hospital_id, session_id, user_ip, user_name, user_phone, user_email) VALUES (:dsid, :did, :hid, :sid, :uip, :uname, :uphone, :uemail)");
    $stmt->execute([
      ':dsid' => $doctor_schedule_id,
      ':did' => $doctor_id,
      ':hid' => $hospital_id,
      ':sid' => $session_id,
      ':uip' => $user_ip,
      ':uname' => $user_name,
      ':uphone' => $user_phone,
      ':uemail' => $user_email
    ]);

    // fetch updated counts
    $stmt = $pdo->prepare("SELECT slots_available FROM doctor_schedule WHERE id = :sid");
    $stmt->execute([':sid'=>$doctor_schedule_id]);
    $updated = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdo->commit();

    echo json_encode([
      'status'=>'ok',
      'message'=>'Appointment successfully booked',
      'doctor_schedule_id' => $doctor_schedule_id,
      'slots_available' => intval($updated['slots_available'])
    ]);

} catch (PDOException $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['status'=>'error','message'=>'Database error','details'=>$e->getMessage()]);
}
