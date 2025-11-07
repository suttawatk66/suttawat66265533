<?php
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

// เขียน SQL Query (เพิ่ม LIMIT 30)
$sql = "
    SELECT gid, prov_nam_t, area, prov_code, 
           ST_AsGeoJSON(ST_Transform(geom,4326)) as geojson
    FROM tha_province
    LIMIT 30;
";

// รัน Query
$result = pg_query($conn, $sql);

$features = array();

while ($row = pg_fetch_assoc($result)) {
    $geometry = json_decode($row['geojson']);
    unset($row['geojson']); // เอา geojson ออกไปเก็บใน geometry แทน

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
header('Content-Type: application/json');
echo json_encode($geojson, JSON_UNESCAPED_UNICODE);

pg_close($conn);
?>
