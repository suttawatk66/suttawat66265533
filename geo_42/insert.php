<?php
$host = "localhost";
$port = "5432";
$dbname = "plk";
$user = "postgres";
$password = "postgres";

$db = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password");
if (!$db) { die("ไม่สามารถเชื่อมต่อฐานข้อมูลได้"); }

$lat  = $_POST['lat']  ?? null;
$lng  = $_POST['lng']  ?? null;
$name = $_POST['name'] ?? null;

if ($lat && $lng && $name) {
    $insert_sql = "
    INSERT INTO points(geom, name) 
    VALUES (ST_SetSRID(ST_Point($lng, $lat), 4326), '$name');
    ";
    $res = pg_query($db, $insert_sql);

    if (!$res) { die("Insert failed: " . pg_last_error($db)); }
}

header('Content-Type: application/json');
echo json_encode(['status'=>'success']);
?>
