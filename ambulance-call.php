<?php
// ambulance-call.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1"/>

  <!-- BootStrap CSS Starts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <!-- BootStrap CSS Ends -->

    <!-- Google Font Starts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <!-- Google Font Ends -->

  <title>Call Ambulance</title>
  <link rel="stylesheet" href="style.css">
  <style>
    body { font-family: system-ui, sans-serif; background:#f6f7fb; padding:20px;}
    .card { max-width:760px; margin:20px auto; background:#fff; padding:16px; border-radius:12px; box-shadow:0 8px 20px rgba(0,0,0,0.06);}
    h2 { margin:0 0 12px; }
    .item { border-bottom:1px solid #eee; padding:12px 0; }
    .item:last-child { border-bottom:none; }
    .name { font-weight:600; }
    .address { font-size:0.9rem; color:#666; margin:4px 0; }
    .phone { margin:6px 0; }
    .btn { display:inline-block; background:#0b63ff; color:white; padding:8px 12px; border-radius:6px; text-decoration:none; font-weight:600;}
    .btn:hover { background:#094fcc; }
    #viewMore { margin-top:16px; display:block; }
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
    <h2>Nearby Ambulance & Hospitals</h2>
    <button id="allowLocation" class="btn">Allow Location</button>
    <div id="list"></div>
    <button id="viewMore" class="btn" style="display:none;">View More</button>
  </div>

  <div class="btn-div">
    <button class="btn btn-primary mt-3"><a href="index.php" class="return-home">Go To Home Page</a></button>
    </div>

<script>
let userLat, userLng;
let offset = 0;
const limit = 4;

document.getElementById('allowLocation').addEventListener('click', () => {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(pos => {
      userLat = pos.coords.latitude;
      userLng = pos.coords.longitude;
      offset = 0;
      loadList();
    }, err => {
      alert("Location access is required.");
    });
  } else {
    alert("Geolocation not supported.");
  }
});

async function loadList() {
  const res = await fetch(`get_ambulances_and_hospitals.php?lat=${userLat}&lng=${userLng}&offset=${offset}&limit=${limit}`);
  const data = await res.json();
  if (data.status !== 'ok') {
    alert(data.message || 'Error loading list');
    return;
  }

  const listEl = document.getElementById('list');
  if (offset === 0) listEl.innerHTML = '';

  data.results.forEach(item => {
    const div = document.createElement('div');
    div.className = 'item';
    div.innerHTML = `
      <div class="name">${item.name}</div>
      ${item.type === 'hospital' ? `<div class="address">${item.address}</div>` : ''}
      <div class="phone">📞 <a class="btn" href="tel:${item.phone}">Call Now</a> ${item.phone}</div>
    `;
    listEl.appendChild(div);
  });

  if (data.hasMore) {
    document.getElementById('viewMore').style.display = 'block';
  } else {
    document.getElementById('viewMore').style.display = 'none';
  }

  offset += limit;
}

document.getElementById('viewMore').addEventListener('click', loadList);
</script>

<!-- Bootstrap JS Starts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script> 
<!-- Bootstrap JS Ends -->
</body>
</html>
