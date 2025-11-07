<?php
// เพิ่ม memory limit เผื่อข้อมูลเยอะ
ini_set('memory_limit', '512M');

// กำหนดค่าการเชื่อมต่อฐานข้อมูล
$host = "localhost";
$dbname = "plk";
$user = "postgres";
$password = "postgres";

// สร้างการเชื่อมต่อ
$conn = pg_connect("host=$host dbname=$dbname user=$user password=$password");
if (!$conn) {
    die("Connection failed: " . pg_last_error());
}

// รับค่าจาก URL และป้องกัน SQL Injection
$lat = pg_escape_string($conn, $_GET['lat']);
$lng = pg_escape_string($conn, $_GET['lng']);
$distance = pg_escape_string($conn, $_GET['distance']);

// SQL Query
$sql = "
    SELECT *,
           ST_AsGeoJSON(ST_Transform(t.geom, 4326)) AS geojson
    FROM tha_tambon t
    WHERE ST_DWithin(
              ST_Transform(ST_SetSRID(ST_MakePoint($lng, $lat), 4326), 3857),
              t.geom,
              $distance
          );
";


// รัน Query
$result = pg_query($conn, $sql);
if (!$result) {
    die("Query failed: " . pg_last_error());
}

$features = array();

// แปลงแต่ละแถวเป็น Feature
while ($row = pg_fetch_assoc($result)) {
    $geometry = json_decode($row['geojson']); // แปลง GeoJSON เป็น object
    unset($row['geojson']); // เอา geojson ออกจาก properties

    $feature = array(
        "type" => "Feature",
        "geometry" => $geometry,
        "properties" => $row
    );
    array_push($features, $feature);
}

// รวมเป็น FeatureCollection
$geojson = array(
    "type" => "FeatureCollection",
    "features" => $features
);

// ส่งออกเป็น JSON
header('Content-Type: application/json; charset=UTF-8');
echo json_encode($geojson, JSON_UNESCAPED_UNICODE);

// ปิดการเชื่อมต่อ
pg_close($conn);
?>