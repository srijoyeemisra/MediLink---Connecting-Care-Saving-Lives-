<?php
// appointments_history.php
require __DIR__ . '/config.php';
session_start();
$session_id = session_id();
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '';

try {
  $config = require __DIR__ . '/config.php';
  $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
  $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

  $stmt = $pdo->prepare("SELECT a.id,a.booked_at,a.status,a.user_name,a.user_phone,a.user_email, ds.date, ds.start_time, ds.end_time, d.name AS doctor_name, d.specialization, h.name AS hospital_name FROM appointments a JOIN doctor_schedule ds ON a.doctor_schedule_id = ds.id JOIN doctors d ON a.doctor_id = d.id JOIN hospital_master h ON a.hospital_id = h.id WHERE a.session_id = :sid OR a.user_ip = :uip ORDER BY a.booked_at DESC");
  $stmt->execute([':sid'=>$session_id, ':uip'=>$user_ip]);
  $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
  http_response_code(500); echo "DB error: ".htmlspecialchars($e->getMessage()); exit;
}
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
 <!-- BootStrap CSS Starts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- BootStrap CSS Ends -->

    <!-- Google Font Starts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Google Font Ends -->

<title>Your Appointments</title>
<link rel="stylesheet" href="style.css">
<style>  
body{font-family:"Roboto", sans-serif;background:#f6f7fb;padding:18px}
.card{max-width:900px;margin:12px auto;background:#fff;padding:16px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.06)}
.appt{border-bottom:1px solid #eee;padding:10px 0}
.muted{color:#666}
.btn-div
    {
        width: 75%;
        text-align: center;
    }
    .return-home, .return-home:hover
    {
        color: white;
        text-decoration: none;
    }
</style>
</head><body>
  <div class="card">
    <h2>Your appointments (session & IP)</h2>
    <?php if (!$rows) { ?>
      <div class="muted">No appointments found for this session or IP address.</div>
    <?php } else {
        foreach ($rows as $r) { ?>
          <div class="appt">
            <div style="font-weight:700"><?= e($r['doctor_name']) ?> — <?= e($r['specialization']) ?></div>
            <div class="muted"><?= e($r['hospital_name']) ?> • <?= e($r['date']) ?> • <?= e($r['start_time']) ?> - <?= e($r['end_time']) ?></div>
            <div>Booked for: <?= e($r['user_name']) ?> • <?= e($r['user_phone']) ?> • <?= e($r['user_email']) ?></div>
            <div class="muted">Status: <?= e($r['status']) ?> • Booked at: <?= e($r['booked_at']) ?></div>
          </div>
    <?php } } ?>
  </div>

  <a href="appointment-schedule.php" style="display:inline-block;margin-top:12px;color:#0b63ff;">← Back to hospitals</a>
  <div class="btn-div">
  <button class="btn btn-primary mt-3"><a href="index.php" class="return-home">Go To Home Page</a></button> 
  </div>
  

  <!-- Bootstrap JS Starts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> 
    <!-- Bootstrap JS Ends -->
</body></html>
