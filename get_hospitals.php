<?php
// get_hospitals.php
// GET parameters:
//  lat (required) - user's latitude
//  lng (required) - user's longitude
//  offset (optional) - integer paging offset (default 0)
//  limit (optional) - items per page (default 4)

header('Content-Type: application/json; charset=utf-8');
// If you need cross-origin access uncomment the next line and set appropriate origin.
// header('Access-Control-Allow-Origin: *');

$config = require __DIR__ . '/config.php';

$lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;
$offset = isset($_GET['offset']) ? intval($_GET['offset']) : 0;
$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 4;

// Validate
if ($lat === null || $lng === null) {
    http_response_code(400);
    echo json_encode(['error' => 'lat and lng parameters are required.']);
    exit;
}

if ($limit <= 0) $limit = 4;
if ($limit > 50) $limit = 50; // safety cap
if ($offset < 0) $offset = 0;

try {
    $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);

    // Haversine formula in SQL to compute distance in kilometers. Earth radius = 6371 km.
    // We select hospitals sorted by distance ASC.
    $sql = "
    SELECT 
      id, name, address, latitude, longitude, phone, beds_available,
      (6371 * 2 * ASIN(SQRT(
         POWER(SIN(RADIANS(latitude - :lat) / 2), 2) +
         COS(RADIANS(:lat)) * COS(RADIANS(latitude)) *
         POWER(SIN(RADIANS(longitude - :lng) / 2), 2)
      ))) AS distance_km
    FROM hospital_master
    ORDER BY distance_km ASC
    LIMIT :offset, :limit
    ";

    $stmt = $pdo->prepare($sql);
    // bind lat/lng normally
    $stmt->bindValue(':lat', $lat);
    $stmt->bindValue(':lng', $lng);
    // offset/limit need to be bound as integers
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Optionally convert numeric strings to proper types
    foreach ($results as &$r) {
        $r['distance_km'] = floatval($r['distance_km']);
        $r['beds_available'] = intval($r['beds_available']);
    }

    echo json_encode([
        'status' => 'ok',
        'count' => count($results),
        'offset' => $offset,
        'limit' => $limit,
        'hospitals' => $results
    ], JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error', 'details' => $e->getMessage()]);
    exit; 
}
