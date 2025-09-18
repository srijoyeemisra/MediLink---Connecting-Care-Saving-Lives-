<?php
// book.php
require __DIR__ . '/config.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    http_response_code(400);
    echo "Invalid hospital id";
    exit;
}

try {
    $config = require __DIR__ . '/config.php';
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

    $stmt = $pdo->prepare("SELECT id, name, address, phone, general_beds, icu_beds FROM hospital_master WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => $id]);
    $h = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$h) {
        http_response_code(404);
        echo "Hospital not found";
        exit;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo "Database error: " . htmlspecialchars($e->getMessage());
    exit;
}

// sanitize for output
function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Book Bed — <?= e($h['name']) ?></title>
  <style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial; background:#f6f7fb; color:#111; padding:20px;}
    .card { max-width:760px; margin:20px auto; background:#fff; padding:18px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.06);}
    h2 { margin:0 0 8px 0; }
    .meta { color:#666; margin-bottom:12px; }
    .counts { display:flex; gap:12px; margin-bottom:16px; }
    .count { background:#f1f5ff; padding:12px; border-radius:8px; min-width:140px; text-align:center; }
    button { background:#0b63ff; color:white; border:0; padding:10px 14px; border-radius:8px; cursor:pointer; font-weight:700; }
    button.secondary { background:transparent; color:#0b63ff; border:1px solid rgba(11,99,255,0.12); }
    .message { margin-top:12px; color:green; }
    .error { margin-top:12px; color:red; }
    a.back { display:inline-block; margin-top:12px; color:#0b63ff; text-decoration:none; }
  </style>
</head>
<body>
  <div class="card" id="card">
    <h2><?= e($h['name']) ?></h2>
    <div class="meta"><?= e($h['address']) ?> • <?= e($h['phone'] ?: 'Phone N/A') ?></div>

    <div class="counts">
      <div class="count">
        <div style="font-size:0.9rem; color:#666;">General Beds</div>
        <div id="generalCount" style="font-size:1.6rem; font-weight:700;"><?= intval($h['general_beds']) ?></div>
      </div>
      <div class="count">
        <div style="font-size:0.9rem; color:#666;">ICU Beds</div>
        <div id="icuCount" style="font-size:1.6rem; font-weight:700;"><?= intval($h['icu_beds']) ?></div>
      </div>
    </div>

    <div style="display:flex; gap:12px;">
      <button id="bookGeneral">Book General Bed</button>
      <button id="bookICU" class="secondary" style="visibility: hidden;">Book ICU Bed</button>
    </div>

    <div class="message" id="message" style="display:none;"></div>
    <div class="error" id="error" style="display:none;"></div>

    <a class="back" href="bed-book.php">← Back to list</a>
  </div>

<script>
(function(){
  const hospitalId = <?= intval($h['id']) ?>;
  const bookGeneralBtn = document.getElementById('bookGeneral');
  const bookICUBtn = document.getElementById('bookICU');
  const generalCountEl = document.getElementById('generalCount');
  const icuCountEl = document.getElementById('icuCount');
  const msgEl = document.getElementById('message');
  const errEl = document.getElementById('error');

  function showMessage(text){
    errEl.style.display = 'none';
    msgEl.style.display = 'block';
    msgEl.textContent = text;
  }
  function showError(text){
    msgEl.style.display = 'none';
    errEl.style.display = 'block';
    errEl.textContent = text;
  }

  async function book(type){
    bookGeneralBtn.disabled = true;
    bookICUBtn.disabled = true;
    showMessage('Processing booking...');
    try {
      const res = await fetch('book_bed.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: hospitalId, type: type })
      });

      let data;
      try {
        data = await res.json();
      } catch (jsonErr) {
        const text = await res.text();
        throw new Error("Invalid JSON response: " + text);
      }

      if (!res.ok || !data || data.status !== 'ok' || !data.hospital) {
        showError((data && data.message) ? data.message : 'Booking failed');
      } else {
        // ✅ Safely update UI counts
        generalCountEl.textContent = data.hospital.general_beds ?? generalCountEl.textContent;
        icuCountEl.textContent = data.hospital.icu_beds ?? icuCountEl.textContent;
        showMessage(data.message);
      }
    } catch (err) {
      showError('Network error: ' + err.message);
    } finally {
      bookGeneralBtn.disabled = false;
      bookICUBtn.disabled = false;
    }
  }

  bookGeneralBtn.addEventListener('click', () => book('general'));
  bookICUBtn.addEventListener('click', () => book('icu'));
})();
</script>

</body>
</html>
