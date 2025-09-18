<?php
// doctors.php
// Shows hospital info + doctors list. Expects ?hospital_id=#
require __DIR__ . '/config.php';
$hid = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;
if ($hid <= 0) { http_response_code(400); echo "Invalid hospital id"; exit; }

try {
  $config = require __DIR__ . '/config.php';
  $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
  $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

  $stmt = $pdo->prepare("SELECT id, name, address, phone FROM hospital_master WHERE id = :id LIMIT 1");
  $stmt->execute([':id'=>$hid]);
  $h = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$h) { http_response_code(404); echo "Hospital not found"; exit; }
} catch (PDOException $e) {
  http_response_code(500); echo "DB error: ".htmlspecialchars($e->getMessage()); exit;
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">

 <!-- BootStrap CSS Starts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- BootStrap CSS Ends -->

    <!-- Google Font Starts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Google Font Ends -->
<title>Doctors — <?= e($h['name']) ?></title>
<link rel="stylesheet" href="style.css">
<style>
  body{font-family:"Roboto", sans-serif;background:#f6f7fb;color:#111;padding:18px}
  .card{max-width:900px;margin:10px auto;background:#fff;padding:16px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.06)}
  .doc{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #eee}
  button{background:#0b63ff;color:#fff;padding:8px 12px;border-radius:8px;border:0;cursor:pointer}
  .muted{color:#666}
  .container{max-width:900px;margin:0 auto}
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
</head>
<body>
  <div class="container">
    <div class="card">
      <h2><?= e($h['name']) ?></h2>
      <div class="muted"><?= e($h['address']) ?> • <?= e($h['phone'] ?: 'Phone N/A') ?></div>
    </div>

    <div id="doctorsList" class="card" style="margin-top:12px;">
      <div class="muted">Loading doctors…</div>
    </div>

    <a href="appointment-schedule.php" style="display:inline-block;margin-top:12px;color:#0b63ff;">← Back to hospital list</a>
    <div class="btn-div">
    <button class="btn btn-primary mt-3"><a href="index.php" class="return-home">Go To Home Page</a></button>
    </div>
  </div>

<script>
(async function(){
  const hospitalId = <?= intval($hid) ?>;
  const listEl = document.getElementById('doctorsList');

  function escapeHtml(s){ if (!s) return ''; return s.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  try {
    const res = await fetch('get_doctors.php?hospital_id=' + encodeURIComponent(hospitalId));
    if (!res.ok) throw new Error('Network response not ok: ' + res.status);
    const data = await res.json();
    if (!data || data.status !== 'ok') throw new Error(data && data.message ? data.message : 'API error');

    const docs = data.doctors || [];
    if (docs.length === 0) {
      listEl.innerHTML = '<div class="muted">No doctors found for this hospital.</div>';
      return;
    }

    listEl.innerHTML = '';
    for (const d of docs) {
      const div = document.createElement('div');
      div.className = 'doc';
      div.innerHTML = `
        <div>
          <div style="font-weight:700">${escapeHtml(d.name)}</div>
          <div class="muted">${escapeHtml(d.specialization)} • ${escapeHtml(d.phone || 'Phone N/A')}</div>
        </div>
        <div>
          <button class="viewDetailsBtn" data-id="${d.id}">View Details</button>
        </div>
      `;
      listEl.appendChild(div);
    }

    // attach handlers
    listEl.querySelectorAll('.viewDetailsBtn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const docId = e.currentTarget.dataset.id;
        if (!docId) return;
        // open schedule modal-like page: we'll navigate to doctor schedule page
        window.location.href = 'doctor_schedule.php?doctor_id=' + encodeURIComponent(docId) + '&hospital_id=' + encodeURIComponent(hospitalId);
      });
    });

  } catch (err) {
    console.error(err);
    listEl.innerHTML = '<div class="muted">Failed to load doctors: ' + (err.message || '') + '</div>';
  }
})();
</script>

<!-- Bootstrap JS Starts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> 
    <!-- Bootstrap JS Ends -->
</body>
</html>
