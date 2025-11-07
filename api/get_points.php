<?php
// api/get_points.php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ใช้ __DIR__ เพื่อให้ได้ path ที่ถูกต้อง
$configPath = __DIR__ . '/db_config.php';
$connectPath = __DIR__ . '/db_connect.php';

// ลบ comment ออก เพราะมันทำให้ JSON parse ไม่ได้
// echo "<!-- Debug: configPath = $configPath -->\n";
// echo "<!-- Debug: connectPath = $connectPath -->\n";

if (!file_exists($configPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration file not found at: ' . $configPath
    ]);
    exit;
}

if (!file_exists($connectPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'Database connection file not found at: ' . $connectPath
    ]);
    exit;
}

require_once $connectPath;

$table = $_GET['table'] ?? 'agi64';
$tables = ['agi64','agi65','agi66','agi67'];

if (!in_array($table, $tables)) {
    echo json_encode([
        'success' => false, 
        'message' => 'ตารางไม่ถูกต้อง'
    ]);
    exit;
}

try {
    $pdo = db_connect();
    
    // ตรวจสอบว่าตารางมีอยู่จริง
    $checkTable = $pdo->prepare("
        SELECT EXISTS (
            SELECT FROM information_schema.tables 
            WHERE table_name = ?
        )
    ");
    $checkTable->execute([$table]);
    $tableExists = $checkTable->fetchColumn();
    
    if (!$tableExists) {
        echo json_encode([
            'success' => false,
            'message' => "ตาราง $table ไม่พบในฐานข้อมูล"
        ]);
        exit;
    }
    
    $stmt = $pdo->prepare("SELECT * FROM \"$table\" ORDER BY s_id");
    $stmt->execute();
    $data = $stmt->fetchAll();

    // ทำความสะอาดข้อมูล - แปลง null เป็น string ว่าง
    $cleanedData = array_map(function($row) {
        return array_map(function($value) {
            return $value === null ? '' : $value;
        }, $row);
    }, $data);

    echo json_encode([
        'success' => true,
        'data' => $cleanedData,
        'count' => count($cleanedData)
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    error_log("Database error in get_points.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: ' . $e->getMessage()
    ]);
} catch (Exception $e) {
    error_log("General error in get_points.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาดทั่วไป: ' . $e->getMessage()
    ]);
}
?>