<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once 'db_connect.php'; 

$tables = ['agi64','agi65','agi66','agi67'];

try {
    // ใช้ฟังก์ชัน db_connect() แทน
    $pdo = db_connect();

    $totalStudents = 0;
    $allFaculties = [];
    $allDepartments = [];
    $allProvinces = [];

    foreach ($tables as $table) {
        $stmt = $pdo->prepare("SELECT * FROM \"$table\"");
        $stmt->execute();
        $students = $stmt->fetchAll();
        
        $totalStudents += count($students);
        
        foreach ($students as $student) {
            if ($student['หลักสูตร']) $allFaculties[] = $student['หลักสูตร'];
            if ($student['ภาควิชา']) $allDepartments[] = $student['ภาควิชา'];
            if ($student['จังหวัด']) $allProvinces[] = $student['จังหวัด'];
        }
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'total_students' => $totalStudents,
            'total_faculties' => count(array_unique($allFaculties)),
            'total_departments' => count(array_unique($allDepartments)),
            'total_provinces' => count(array_unique($allProvinces))
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>