<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>

  <!-- Google Font Starts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
  <!-- Google Font Ends -->

  <!-- BootStrap CSS Starts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- BootStrap CSS Ends -->

  <title>Hospital Bed Booking — Nearby Hospitals</title>
  <style>
    /* Minimal, clean styles */
    body 
    { 
      font-family:"Roboto", sans-serif;
      margin: 0;
      padding: 0; 
      background:#f6f7fb; 
      color:#111;
    }
    .wrap 
    { 
      max-width: 900px; 
      margin: 28px auto; padding: 18px; 
    }
    header 
    { display:flex; align-items:center; justify-content:space-between; margin-bottom: 12px; 
    }
    h1 
    { margin:0; font-size:1.2rem; 
    }
    .status 
    { font-size:0.9rem; color:#666; 
    }
    .list 
    { margin-top:12px; display:grid; gap:10px; 
    }
    .card 
    { background:white; border-radius:10px; padding:12px; box-shadow:0 6px 18px rgba(16,24,40,0.06); display:flex; justify-content:space-between; align-items:flex-start; 
    }
    .info 
    { max-width:70%; 
    }
    .name 
    { font-weight:600; margin-bottom:6px; 
    }
    .meta 
    { color:#666; font-size:0.9rem; margin-bottom:6px; 
    }
    .distance 
    { font-size:0.9rem; color:#0b63ff; font-weight:600; 
    }
    .actions 
    { display:flex; gap:8px; align-items:center; 
    }
    button 
    { background:#0b63ff; color:white; border:0; padding:8px 12px; border-radius:8px; cursor:pointer; font-weight:600; 
    }
    button.secondary 
    { background:transparent; color:#0b63ff; border:1px solid rgba(11,99,255,0.12); 
    }
    .muted 
    { color:#999; 
      font-size:0.9rem; 
    }
    .loader 
    { margin-top:10px; 
    }
    .manual 
    { margin-top:12px; font-size:0.95rem; color:#333; 
    }
    @media (max-width:560px)
    {
      .info { max-width:60%; }
      h1 { font-size:1rem; }
    }
  </style>
</head>
<body>
  <div class="wrap">
    <header>
      <h1>Find nearest hospitals</h1>
      <div class="status" id="status">Requesting location…</div>
    </header>

    <div id="controls">
      <button id="locButton">Allow location</button>
      <button id="manualEntryBtn" class="secondary">Enter coords manually</button>
    </div>

    <div id="manualForm" style="display:none; margin-top:12px;">
      <label class="muted">Latitude: <input id="manualLat" type="number" step="any" /></label>
      <label class="muted" style="margin-left:8px;">Longitude: <input id="manualLng" type="number" step="any" /></label>
      <button id="manualSubmit" style="margin-left:8px;">Load</button>
    </div>

    <div class="list" id="hospitalList" aria-live="polite"></div>

    <div style="margin-top:12px;">
      <button id="viewMore" style="display:none;">View more</button>
      <div class="loader" id="loader" style="display:none;">Loading…</div>
      <div id="endMsg" style="display:none; margin-top:10px;" class="muted">No more hospitals nearby.</div>
    </div>
  </div>

<script>
(() => {
  const apiEndpoint = 'get_hospitals.php';
  const limit = 4; // per page
  let offset = 0;
  let userLat = null;
  let userLng = null;
  let loading = false;
  const statusEl = document.getElementById('status');
  const listEl = document.getElementById('hospitalList');
  const viewMoreBtn = document.getElementById('viewMore');
  const loader = document.getElementById('loader');
  const endMsg = document.getElementById('endMsg');

  const locButton = document.getElementById('locButton');
  const manualEntryBtn = document.getElementById('manualEntryBtn');
  const manualForm = document.getElementById('manualForm');
  const manualSubmit = document.getElementById('manualSubmit');

  function setStatus(text) { statusEl.textContent = text; }

  function showLoader(show){
    loading = show;
    loader.style.display = show ? 'block' : 'none';
    viewMoreBtn.disabled = show;
  }

  function formatDistance(km){
    if (km < 1) return Math.round(km * 1000) + ' m';
    return (Math.round(km * 10) / 10) + ' km';
  }

  function renderHospitals(items){
    for (const h of items){
      const card = document.createElement('div');
      card.className = 'card';
      card.innerHTML = `
        <div class="info">
          <div class="name">${escapeHtml(h.name)}</div>
          <div class="meta">${escapeHtml(h.address || 'Address not available')} • ${h.phone || 'Phone N/A'}</div>
          <div class="muted">Beds available: ${h.beds_available}</div>
        </div>
        <div class="actions">
          <div class="distance">${formatDistance(h.distance_km)}</div>
          <div style="display:flex; flex-direction:column; gap:8px; margin-left:8px;">
            <button class="secondary bookBtn" data-id="${h.id}">Book bed</button>
            <button class="secondary detailsBtn" data-id="${h.id}">View details</button>
          </div>
        </div>
      `;
      listEl.appendChild(card);
    }
    // attach simple click handlers (could be improved)
    listEl.querySelectorAll('.bookBtn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        alert('Proceed to booking flow for hospital id: ' + id + '\\n(Implement booking flow separately.)');
      });
    });
    listEl.querySelectorAll('.detailsBtn').forEach(btn => {
      btn.addEventListener('click', (e) => {
        const id = e.currentTarget.dataset.id;
        alert('Show details for hospital id: ' + id + '\\n(Implement details UI separately.)');
      });
    });
  }

  function escapeHtml(s){
    if (!s) return '';
    return s.replace(/[&<>"]/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]));
  }

  async function loadMore(){
    if (loading) return;
    showLoader(true);
    setStatus('Loading hospitals…');
    try {
      const params = new URLSearchParams({
        lat: userLat,
        lng: userLng,
        offset: offset,
        limit: limit
      });
      const res = await fetch(apiEndpoint + '?' + params.toString());
      if (!res.ok) throw new Error('Network response not ok: ' + res.status);
      const data = await res.json();
      if (data.status !== 'ok') throw new Error(data.error || 'API error');
      if (data.hospitals && data.hospitals.length > 0) {
        renderHospitals(data.hospitals);
        offset += data.hospitals.length;
        viewMoreBtn.style.display = 'inline-block';
        endMsg.style.display = 'none';
      } else {
        // no more results
        viewMoreBtn.style.display = 'none';
        if (offset === 0) {
          listEl.innerHTML = '<div class="muted">No hospitals found near your location.</div>';
        } else {
          endMsg.style.display = 'block';
        }
      }
      setStatus(`Showing nearest hospitals (loaded ${offset})`);
    } catch (err) {
      console.error(err);
      setStatus('Failed to load hospitals: ' + err.message);
    } finally {
      showLoader(false);
    }
  }

  // initial request once we have coords
  function initWithCoords(lat, lng){
    userLat = lat;
    userLng = lng;
    offset = 0;
    listEl.innerHTML = '';
    viewMoreBtn.style.display = 'none';
    endMsg.style.display = 'none';
    setStatus('Location accepted — loading nearest hospitals');
    loadMore();
  }

  locButton.addEventListener('click', () => {
    setStatus('Requesting location…');
    if (!navigator.geolocation) {
      setStatus('Geolocation not supported in your browser.');
      return;
    }
    navigator.geolocation.getCurrentPosition(pos => {
      const {latitude, longitude} = pos.coords;
      initWithCoords(latitude, longitude);
    }, err => {
      console.warn('Location error', err);
      setStatus('Location access denied or unavailable. Use manual entry.');
      manualForm.style.display = 'block';
    }, { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 });
  });

  manualEntryBtn.addEventListener('click', () => {
    manualForm.style.display = manualForm.style.display === 'none' ? 'block' : 'none';
  });

  manualSubmit.addEventListener('click', () => {
    const lat = parseFloat(document.getElementById('manualLat').value);
    const lng = parseFloat(document.getElementById('manualLng').value);
    if (Number.isFinite(lat) && Number.isFinite(lng)) {
      initWithCoords(lat, lng);
    } else {
      alert('Please enter valid latitude and longitude.');
    }
  });

  viewMoreBtn.addEventListener('click', () => {
    loadMore();
  });

  // Auto-click to request location on page load (optional)
  // locButton.click();

})();
</script>
</body>
</html>
