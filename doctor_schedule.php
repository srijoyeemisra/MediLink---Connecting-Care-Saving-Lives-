<?php
// doctor_schedule.php

session_start();
$userName = $_SESSION['fullname'];
$userPhone = $_SESSION['phone'];
$userEmail = $_SESSION['email'];

// Expects ?doctor_id=...&hospital_id=...
require __DIR__ . '/config.php';
$doctor_id = isset($_GET['doctor_id']) ? intval($_GET['doctor_id']) : 0;
$hospital_id = isset($_GET['hospital_id']) ? intval($_GET['hospital_id']) : 0;
if ($doctor_id <= 0 || $hospital_id <= 0) { http_response_code(400); echo "Invalid parameters"; exit; }

try {
  $config = require __DIR__ . '/config.php';
  $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
  $pdo = new PDO($dsn, $config['user'], $config['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

  // fetch doctor & hospital basic info
  $stmt = $pdo->prepare("SELECT d.id,d.name,d.specialization,d.phone,h.name AS hospital_name, h.address, h.phone AS hospital_phone FROM doctors d JOIN hospital_master h ON d.hospital_id=h.id WHERE d.id = :did LIMIT 1");
  $stmt->execute([':did'=>$doctor_id]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);
  if (!$row) { http_response_code(404); echo "Doctor not found"; exit; }
} catch (PDOException $e) {
  http_response_code(500); echo "DB error: ".htmlspecialchars($e->getMessage()); exit;
}

function e($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }

?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">

 <!-- BootStrap CSS Starts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- BootStrap CSS Ends -->

    <!-- Google Font Starts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Google Font Ends -->
<title>Schedule — <?= e($row['name']) ?></title>
<link rel="stylesheet" href="style.css">
<style>
  body{font-family:"Roboto", sans-serif;background:#f6f7fb;color:#111;padding:18px}
  .card{max-width:900px;margin:10px auto;background:#fff;padding:16px;border-radius:10px;box-shadow:0 6px 18px rgba(16,24,40,0.06)}
  .slot{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid #eee}
  button{background:#0b63ff;color:#fff;padding:8px 12px;border-radius:8px;border:0;cursor:pointer}
  button.secondary{background:transparent;color:#0b63ff;border:1px solid rgba(11,99,255,0.12)}
  .muted{color:#666}
  .form-row{margin:12px 0}
  .input{padding:8px;border-radius:6px;border:1px solid #ddd;width:100%}
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
  <div class="card">
    <h2><?= e($row['name']) ?> — <span class="muted"><?= e($row['specialization']) ?></span></h2>
    <div class="muted"><?= e($row['hospital_name']) ?> • <?= e($row['hospital_phone'] ?: 'Phone N/A') ?></div>
  </div>

  <div id="scheduleList" class="card" style="margin-top:12px;">
    <div class="muted">Loading available dates…</div>
  </div>

  <a href="doctors.php?hospital_id=<?= intval($hospital_id) ?>" style="display:inline-block;margin-top:12px;color:#0b63ff;">← Back to doctors</a>

  

<!-- Booking form modal-ish area (simple inline form) -->
<div id="bookingArea" style="max-width:900px;margin:16px auto; display:none;">
  <div class="card">
    <h3 id="bookingHeader">Book appointment</h3>
    <div class="form-row">
      <label style="display:block;margin-bottom:6px">Your name</label>
      <input id="userName" class="input" placeholder="Full name" type="text" name="userName" value="<?php echo htmlspecialchars($userName) ?>" autocomplete="new-userName" required>
    </div>
    <div class="form-row">
      <label style="display:block;margin-bottom:6px">Phone</label>
      <input id="userPhone" class="input" placeholder="Phone number" type="number" name="userPhone" value="<?php echo htmlspecialchars($userPhone) ?>" autocomplete="new-phone" required>
    </div>
    <div class="form-row">
      <label style="display:block;margin-bottom:6px">Email</label>
      <input id="userEmail" class="input" placeholder="Email" type="email" name="userEmail" value="<?php echo htmlspecialchars($userEmail) ?>" autocomplete="new-email" required>
    </div>
    <div style="display:flex;gap:12px;">
      <button id="confirmBook">Schedule Appointment</button>
      <button id="cancelBook" class="secondary">Cancel</button>
    </div>
    <div id="bookMsg" style="margin-top:10px;color:green;display:none;"></div>
    <div id="bookErr" style="margin-top:10px;color:red;display:none;"></div>
  </div>
</div>

<div class="btn-div">
    <button class="btn btn-primary mt-3"><a href="index.php" class="return-home">Go To Home Page</a></button>
    </div>

<script>
(function(){
  const doctorId = <?= intval($doctor_id) ?>;
  const hospitalId = <?= intval($hospital_id) ?>;
  const scheduleList = document.getElementById('scheduleList');
  const bookingArea = document.getElementById('bookingArea');
  const bookingHeader = document.getElementById('bookingHeader');
  const userName = document.getElementById('userName');
  const userPhone = document.getElementById('userPhone');
  const userEmail = document.getElementById('userEmail');
  const confirmBookBtn = document.getElementById('confirmBook');
  const cancelBookBtn = document.getElementById('cancelBook');
  const bookMsg = document.getElementById('bookMsg');
  const bookErr = document.getElementById('bookErr');

  let selectedScheduleId = null;
  let selectedDateText = '';

  function escapeHtml(s){ if (!s) return ''; return s.replace(/[&<>"]/g, c=> ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c])); }

  async function loadSchedule(){
    scheduleList.innerHTML = '<div class="muted">Loading available dates…</div>';
    try {
      const res = await fetch('get_doctor_schedule.php?doctor_id=' + encodeURIComponent(doctorId));
      if (!res.ok) throw new Error('Network response not ok: ' + res.status);
      const data = await res.json();
      if (!data || data.status !== 'ok') throw new Error(data && data.message ? data.message : 'API error');
      const rows = data.schedule || [];
      if (rows.length === 0) {
        scheduleList.innerHTML = '<div class="muted">No upcoming dates available.</div>';
        return;
      }
      scheduleList.innerHTML = '';
      for (const r of rows) {
        const div = document.createElement('div');
        div.className = 'slot';
        const dateStr = r.date;
        const timeRange = r.start_time + ' - ' + r.end_time;
        div.innerHTML = `
          <div>
            <div style="font-weight:700">${escapeHtml(dateStr)}</div>
            <div class="muted">${escapeHtml(timeRange)}</div>
            <div class="muted">Available: <span class="availCount">${r.slots_available}</span> / ${r.slots_total}</div>
          </div>
          <div>
            <button class="scheduleBtn" data-id="${r.id}" ${r.slots_available<=0 ? 'disabled' : ''}>Schedule Appointment</button>
          </div>
        `;
        scheduleList.appendChild(div);
      }

      // attach handlers
      scheduleList.querySelectorAll('.scheduleBtn').forEach(btn => {
        btn.addEventListener('click', (e) => {
          const sid = e.currentTarget.dataset.id;
          if (!sid) return;
          selectedScheduleId = sid;
          selectedDateText = e.currentTarget.closest('.slot').querySelector('div > div').textContent || '';
          bookingHeader.textContent = 'Book appointment — ' + selectedDateText;
          bookingArea.style.display = 'block';

          // prefill if possible from sessionStorage    
                
          bookMsg.style.display = 'none'; bookErr.style.display = 'none';
        });
      });

    } catch (err) {
      console.error(err);
      scheduleList.innerHTML = '<div class="muted">Failed to load schedule: ' + (err.message||'') + '</div>';
    }
  }

  async function bookAppointment(){
    if (!selectedScheduleId) return;
    const name = (userName.value || '').trim();
    const phone = (userPhone.value || '').trim();
    const email = (userEmail.value || '').trim();
    if (!name || !phone) { bookErr.style.display='block'; bookErr.textContent='Name and phone are required'; return; }
    // save to sessionStorage for convenience
    sessionStorage.setItem('appt_user_name', name);
    sessionStorage.setItem('appt_user_phone', phone);
    sessionStorage.setItem('appt_user_email', email);

    confirmBookBtn.disabled = true;
    cancelBookBtn.disabled = true;
    bookMsg.style.display='block'; bookMsg.textContent='Processing...';
    bookErr.style.display='none';

    try {
      const payload = {
        doctor_schedule_id: parseInt(selectedScheduleId,10),
        doctor_id: parseInt(doctorId,10),
        hospital_id: parseInt(hospitalId,10),
        user_name: name,
        user_phone: phone,
        user_email: email
      };
      const res = await fetch('book_appointment.php', {
        method:'POST',
        headers:{ 'Content-Type':'application/json' },
        body: JSON.stringify(payload)
      });
      let data;
      try { data = await res.json(); } catch(e) {
        const txt = await res.text();
        throw new Error('Invalid JSON response: ' + txt);
      }
      if (!res.ok || !data || data.status !== 'ok') {
        throw new Error(data && data.message ? data.message : 'Booking failed');
      }

      // Success: update UI instantly — change button to "Appointment Booked" and decrement count displayed
      bookMsg.style.display='block'; bookMsg.textContent = data.message || 'Appointment successfully booked';
      // find the slot element matching selectedScheduleId and update its availCount and button
      const btn = scheduleList.querySelector('.scheduleBtn[data-id="'+selectedScheduleId+'"]');
      if (btn) {
        const slotDiv = btn.closest('.slot');
        const availEl = slotDiv.querySelector('.availCount');
        if (availEl) availEl.textContent = (parseInt(availEl.textContent||'0',10) - 1);
        btn.textContent = 'Appointment Booked';
        btn.disabled = true;
      }

    } catch (err) {
      console.error(err);
      bookErr.style.display='block';
      bookErr.textContent = 'Error: ' + (err.message || '');
    } finally {
      confirmBookBtn.disabled = false;
      cancelBookBtn.disabled = false;
    }
  }

  confirmBookBtn.addEventListener('click', bookAppointment);
  cancelBookBtn.addEventListener('click', () => {
    bookingArea.style.display = 'none';
  });

  // load on page load
  loadSchedule();
})();
</script>

<!-- Bootstrap JS Starts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> 
    <!-- Bootstrap JS Ends -->
</body>
</html>
