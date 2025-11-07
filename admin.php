<?php
session_start();
require_once 'auth.php';
require_login();

// โหลดการเชื่อมต่อฐานข้อมูลจากไฟล์ db_connect.php
require_once 'db_connect.php';

// ตารางที่ต้องใช้งาน
$tables = ['agi64', 'agi65', 'agi66', 'agi67'];

// ตัวอย่างการเชื่อมต่อฐานข้อมูล
$pdo = db_connect(); // เรียกฟังก์ชันจาก db_connect.php

// Handle form actions
$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $table = $_POST['table'] ?? '';
    
    if (!in_array($table, $tables)) {
        $message = 'ตารางไม่ถูกต้อง';
        $message_type = 'danger';
    } else {
        try {
            if ($action === 'create') {
                // กำหนด mapping ชื่อฟิลด์ไทย -> อังกฤษสำหรับ parameters
                $field_mapping = [
                    's_id' => 's_id',
                    's_name' => 's_name',
                    'หลักสูตร' => 'curriculum',
                    'คณะ' => 'faculty',
                    'ภาควิชา' => 'department', 
                    'จบจากโรงเรียน' => 'school',
                    'lat' => 'lat',
                    'long' => 'long',
                    'ตำบล' => 'subdistrict',
                    'อำเภอ' => 'district',
                    'จังหวัด' => 'province'
                ];
                
                $columns = [];
                $values = [];
                $params = [];
                
                foreach ($_POST as $key => $value) {
                    if ($key !== 'action' && $key !== 'table') {
                        // ใช้ชื่อคอลัมน์จริงใน DB (ภาษาไทย) สำหรับ INSERT
                        $columns[] = "\"$key\"";
                        // ใช้ชื่อ parameter ภาษาอังกฤษ
                        $eng_key = $field_mapping[$key] ?? $key;
                        $values[] = ":$eng_key";
                        $params[":$eng_key"] = $value;
                    }
                }
                
                if (!empty($columns)) {
                    $sql = "INSERT INTO \"$table\" (" . implode(',', $columns) . ") VALUES (" . implode(',', $values) . ")";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute($params);
                    $message = 'เพิ่มข้อมูลสำเร็จ!';
                    $message_type = 'success';
                }
                
            } elseif ($action === 'update') {
                $student_id = $_POST['student_id'] ?? '';
                if ($student_id) {
                    // กำหนด mapping ชื่อฟิลด์ไทย -> อังกฤษสำหรับ parameters
                    $field_mapping = [
                        's_name' => 's_name',
                        'หลักสูตร' => 'curriculum',
                        'คณะ' => 'faculty',
                        'ภาควิชา' => 'department',
                        'จบจากโรงเรียน' => 'school',
                        'lat' => 'lat',
                        'long' => 'long',
                        'ตำบล' => 'subdistrict',
                        'อำเภอ' => 'district',
                        'จังหวัด' => 'province'
                    ];
                    
                    $updates = [];
                    $params = [':s_id' => $student_id];
                    
                    foreach ($_POST as $key => $value) {
                        if (!in_array($key, ['action', 'table', 'student_id'])) {
                            $eng_key = $field_mapping[$key] ?? $key;
                            $updates[] = "\"$key\" = :$eng_key";
                            $params[":$eng_key"] = $value;
                        }
                    }
                    
                    if (!empty($updates)) {
                        $sql = "UPDATE \"$table\" SET " . implode(', ', $updates) . " WHERE s_id = :s_id";
                        $stmt = $pdo->prepare($sql);
                        $stmt->execute($params);
                        $message = 'อัพเดทข้อมูลสำเร็จ!';
                        $message_type = 'success';
                    }
                }
                
            } elseif ($action === 'delete') {
                $student_id = $_POST['student_id'] ?? '';
                if ($student_id) {
                    $sql = "DELETE FROM \"$table\" WHERE s_id = :s_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':s_id' => $student_id]);
                    $message = 'ลบข้อมูลสำเร็จ!';
                    $message_type = 'success';
                }
                
            } elseif ($action === 'update_coordinates') {
                // การอัพเดทพิกัดจากแผนที่
                $student_id = $_POST['student_id'] ?? '';
                $lat = $_POST['lat'] ?? '';
                $long = $_POST['long'] ?? '';
                
                if ($student_id && $lat && $long) {
                    $sql = "UPDATE \"$table\" SET lat = :lat, long = :long WHERE s_id = :s_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([
                        ':lat' => $lat,
                        ':long' => $long,
                        ':s_id' => $student_id
                    ]);
                    
                    // ส่ง response สำหรับ AJAX
                    if (isset($_POST['ajax'])) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'อัพเดทพิกัดสำเร็จ!']);
                        exit;
                    }
                    
                    $message = 'อัพเดทพิกัดสำเร็จ!';
                    $message_type = 'success';
                }
                
            } elseif ($action === 'delete_coordinates') {
                // การลบพิกัดจากแผนที่
                $student_id = $_POST['student_id'] ?? '';
                
                if ($student_id) {
                    $sql = "UPDATE \"$table\" SET lat = NULL, long = NULL WHERE s_id = :s_id";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([':s_id' => $student_id]);
                    
                    // ส่ง response สำหรับ AJAX
                    if (isset($_POST['ajax'])) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'message' => 'ลบพิกัดสำเร็จ!']);
                        exit;
                    }
                    
                    $message = 'ลบพิกัดสำเร็จ!';
                    $message_type = 'success';
                }
            }
        } catch (PDOException $e) {
            $message = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
            $message_type = 'danger';
            error_log("Database Error: " . $e->getMessage());
            
            if (isset($_POST['ajax'])) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
                exit;
            }
        }
    }
}

// Get current table data
$current_table = $_GET['table'] ?? '';
$is_overview = empty($current_table);

// Fetch data for current table if not overview
if (!$is_overview && in_array($current_table, $tables)) {
    try {
        $stmt = $pdo->prepare("SELECT * FROM \"$current_table\" ORDER BY s_id");
        $stmt->execute();
        $students = $stmt->fetchAll();
    } catch (PDOException $e) {
        $students = [];
        $message = 'ไม่สามารถโหลดข้อมูล: ' . $e->getMessage();
        $message_type = 'danger';
    }
} else {
    $students = [];
}

// สำหรับหน้า overview - ดึงข้อมูลสถิติทั้งหมด
$overview_stats = [];
$table_details = [];

foreach ($tables as $table) {
    try {
        // นับจำนวนนิสิต
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM \"$table\"");
        $stmt->execute();
        $count = $stmt->fetch()['total'];
        $overview_stats[$table] = $count;
        
        // ดึงข้อมูลสำหรับแผนที่ (เฉพาะที่มีพิกัด)
        $stmt = $pdo->prepare("SELECT *, '$table' as table_name FROM \"$table\" WHERE lat IS NOT NULL AND long IS NOT NULL AND lat != 0 AND long != 0");
        $stmt->execute();
        $table_details[$table] = $stmt->fetchAll();
        
    } catch (PDOException $e) {
        $overview_stats[$table] = 0;
        $table_details[$table] = [];
    }
}

// รวมข้อมูลทั้งหมดสำหรับแผนที่
$all_map_data = [];
foreach ($table_details as $table_data) {
    $all_map_data = array_merge($all_map_data, $table_data);
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>จัดการข้อมูลนิสิต - ระบบบริหาร</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        :root {
            --primary: #3498db;
            --success: #27ae60;
            --warning: #f39c12;
            --danger: #e74c3c;
            --dark: #2c3e50;
            --light: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar {
            background: linear-gradient(135deg, var(--dark), #34495e);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background: white;
            min-height: calc(100vh - 56px);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 56px;
        }
        
        .sidebar .nav-link {
            color: var(--dark);
            padding: 12px 20px;
            margin: 5px 10px;
            border-radius: 8px;
            transition: all 0.3s;
            border: none;
            text-align: left;
        }
        
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: var(--primary);
            color: white;
        }
        
        .main-content {
            padding: 20px;
            background: #f8f9fa;
            min-height: calc(100vh - 56px);
        }
        
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            background: white;
        }
        
        .stat-card {
            text-align: center;
            padding: 25px 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s, box-shadow 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        }
        
        .stat-card.agi64 { border-left-color: #e74c3c; }
        .stat-card.agi65 { border-left-color: #3498db; }
        .stat-card.agi66 { border-left-color: #2ecc71; }
        .stat-card.agi67 { border-left-color: #f39c12; }
        
        .stat-card .number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .stat-card.agi64 .number { color: #e74c3c; }
        .stat-card.agi65 .number { color: #3498db; }
        .stat-card.agi66 .number { color: #2ecc71; }
        .stat-card.agi67 .number { color: #f39c12; }
        
        .table th {
            background: var(--dark);
            color: white;
        }
        
        .action-buttons .btn {
            padding: 6px 10px;
            margin: 2px;
            border-radius: 4px;
        }
        
        .section-title {
            color: var(--dark);
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 3px solid var(--primary);
        }
        
        .map-container {
            height: 600px;
            border-radius: 10px;
            overflow: hidden;
            border: 2px solid #e9ecef;
        }
        
        .table-controls {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .grade-badge {
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 20px;
        }
        
        .chart-container {
            height: 400px;
            position: relative;
        }
        
        .overview-header {
            background: linear-gradient(135deg, var(--primary), #2980b9);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .map-legend {
            background: white;
            padding: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: absolute;
            top: 10px;
            right: 10px;
            z-index: 1000;
        }
        
        .legend-item {
            display: flex;
            align-items: center;
            margin-bottom: 8px;
            font-size: 12px;
        }
        
        .legend-color {
            width: 15px;
            height: 15px;
            border-radius: 50%;
            margin-right: 8px;
            border: 2px solid white;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        
        .map-controls {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1000;
            background: white;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .temp-marker {
            animation: pulse 1.5s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        
        .popup-edit-btn {
            margin-top: 10px;
            width: 100%;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <i class="fas fa-database me-2"></i>
                ระบบจัดการข้อมูลนิสิต
            </a>
            <div class="navbar-nav ms-auto">
                <span class="navbar-text me-3">
                    <i class="fas fa-user me-1"></i>
                    <?php echo htmlspecialchars($_SESSION['user']['display_name'] ?? $_SESSION['user']['username']); ?>
                </span>
                <a href="student.html" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-eye me-1"></i>ดูข้อมูล
                </a>
                <a href="logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>ออกจากระบบ
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar">
                <div class="d-flex flex-column flex-shrink-0 p-3">
                    <ul class="nav nav-pills flex-column mb-auto">
                        <li class="nav-item">
                            <a href="admin.php" class="nav-link <?php echo $is_overview ? 'active' : ''; ?>">
                                <i class="fas fa-tachometer-alt me-2"></i>
                                ภาพรวมระบบ
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="admin.php?table=agi64" class="nav-link <?php echo $current_table === 'agi64' ? 'active' : ''; ?>">
                                <i class="fas fa-users me-2"></i>
                                ข้อมูล AGI64
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="admin.php?table=agi65" class="nav-link <?php echo $current_table === 'agi65' ? 'active' : ''; ?>">
                                <i class="fas fa-users me-2"></i>
                                ข้อมูล AGI65
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="admin.php?table=agi66" class="nav-link <?php echo $current_table === 'agi66' ? 'active' : ''; ?>">
                                <i class="fas fa-users me-2"></i>
                                ข้อมูล AGI66
                            </a>
                        </li>
                        <li class="nav-item">
                            <a href="admin.php?table=agi67" class="nav-link <?php echo $current_table === 'agi67' ? 'active' : ''; ?>">
                                <i class="fas fa-users me-2"></i>
                                ข้อมูล AGI67
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($is_overview): ?>
                    <!-- หน้า Overview - แสดงแผนที่และสถิติ -->
                    <div class="overview-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="h3 mb-2">
                                    <i class="fas fa-tachometer-alt me-2"></i>
                                    ภาพรวมระบบ
                                </h1>
                                <p class="mb-0 opacity-75">ระบบจัดการข้อมูลนิสิตคณะเกษตรศาสตร์</p>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="display-4 fw-bold"><?php echo array_sum($overview_stats); ?></div>
                                <small>นิสิตทั้งหมดในระบบ</small>
                            </div>
                        </div>
                    </div>

                    <!-- สถิติแยกตามชั้นปี -->
                    <div class="row mb-4">
                        <?php foreach ($tables as $table): ?>
                        <div class="col-md-3">
                            <div class="stat-card <?php echo $table; ?>">
                                <div class="number"><?php echo $overview_stats[$table]; ?></div>
                                <div class="text-muted">นิสิต <?php echo strtoupper($table); ?></div>
                                <div class="mt-3">
                                    <a href="admin.php?table=<?php echo $table; ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-cog me-1"></i>จัดการข้อมูล
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- แผนที่และตัวควบคุม -->
                    <div class="table-controls">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="mb-0 text-dark">
                                    <i class="fas fa-map-marked-alt me-2"></i>
                                    แผนที่แสดงที่อยู่นิสิต
                                </h5>
                                <small class="text-muted">ดับเบิลคลิกบนแผนที่เพื่อเพิ่มจุด • คลิกที่จุดเพื่อแก้ไข</small>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex gap-2 justify-content-end">
                                    <select id="map-grade-select" class="form-select w-auto">
                                        <option value="all">แสดงทั้งหมด</option>
                                        <option value="agi64">เฉพาะ AGI64</option>
                                        <option value="agi65">เฉพาะ AGI65</option>
                                        <option value="agi66">เฉพาะ AGI66</option>
                                        <option value="agi67">เฉพาะ AGI67</option>
                                    </select>
                                    <button id="refresh-map" class="btn btn-primary">
                                        <i class="fas fa-sync-alt me-1"></i>โหลดใหม่
                                    </button>
                                    <button id="add-point-mode" class="btn btn-outline-success">
                                        <i class="fas fa-plus me-1"></i>โหมดเพิ่มจุด
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- แผนที่ -->
                    <div class="card">
                        <div class="card-body p-0 position-relative">
                            <div id="overview-map" class="map-container"></div>
                            
                            <!-- Map Controls -->
                            <div class="map-controls">
                                <div class="btn-group-vertical">
                                    <button id="zoom-in" class="btn btn-sm btn-light border">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                    <button id="zoom-out" class="btn btn-sm btn-light border">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                    <button id="locate-th" class="btn btn-sm btn-light border" title="กลับไปประเทศไทย">
                                        <i class="fas fa-globe-asia"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="map-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #e74c3c;"></div>
                                    <span>AGI64</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #3498db;"></div>
                                    <span>AGI65</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #2ecc71;"></div>
                                    <span>AGI66</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #f39c12;"></div>
                                    <span>AGI67</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ตารางสรุป -->
                    <div class="card mt-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0">
                                <i class="fas fa-chart-bar me-2"></i>
                                สรุปข้อมูลทั้งหมด
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ชั้นปี</th>
                                            <th>จำนวนนิสิต</th>
                                            <th>จำนวนหลักสูตร</th>
                                            <th>จำนวนจังหวัด</th>
                                            <th>จำนวนที่มีพิกัด</th>
                                            <th>สถานะ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($tables as $table): ?>
                                        <?php 
                                        try {
                                            $stmt = $pdo->prepare("SELECT COUNT(DISTINCT \"หลักสูตร\") as curriculums, COUNT(DISTINCT \"จังหวัด\") as provinces FROM \"$table\"");
                                            $stmt->execute();
                                            $stats = $stmt->fetch();
                                            $with_coords = count($table_details[$table]);
                                        } catch (PDOException $e) {
                                            $stats = ['curriculums' => 0, 'provinces' => 0];
                                            $with_coords = 0;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo strtoupper($table); ?></strong>
                                            </td>
                                            <td>
                                                <span class="badge bg-primary fs-6"><?php echo $overview_stats[$table]; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success"><?php echo $stats['curriculums']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?php echo $stats['provinces']; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning text-dark"><?php echo $with_coords; ?></span>
                                            </td>
                                            <td>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check me-1"></i>พร้อมใช้งาน
                                                </span>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                        <tr class="table-primary fw-bold">
                                            <td>รวมทั้งหมด</td>
                                            <td><?php echo array_sum($overview_stats); ?></td>
                                            <td>-</td>
                                            <td>-</td>
                                            <td><?php echo count($all_map_data); ?></td>
                                            <td>ระบบทำงานปกติ</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- หน้าจัดการข้อมูลตามตาราง -->
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="section-title m-0">
                            <i class="fas fa-users me-2"></i>
                            จัดการข้อมูลนิสิต - <?php echo strtoupper($current_table); ?>
                        </h2>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addStudentModal">
                            <i class="fas fa-plus me-1"></i>เพิ่มนิสิตใหม่
                        </button>
                    </div>

                    <!-- Statistics -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="number"><?php echo count($students); ?></div>
                                <div class="text-muted">จำนวนนิสิต</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="number">
                                    <?php 
                                    $curriculums = array_unique(array_column($students, 'หลักสูตร'));
                                    echo count(array_filter($curriculums));
                                    ?>
                                </div>
                                <div class="text-muted">จำนวนหลักสูตร</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="number">
                                    <?php 
                                    $provinces = array_unique(array_column($students, 'จังหวัด'));
                                    echo count(array_filter($provinces));
                                    ?>
                                </div>
                                <div class="text-muted">จำนวนจังหวัด</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="stat-card">
                                <div class="number">
                                    <?php 
                                    $schools = array_unique(array_column($students, 'จบจากโรงเรียน'));
                                    echo count(array_filter($schools));
                                    ?>
                                </div>
                                <div class="text-muted">จำนวนโรงเรียน</div>
                            </div>
                        </div>
                    </div>

                    <!-- Students Table -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <span>
                                <i class="fas fa-list me-2"></i>
                                รายชื่อนิสิตทั้งหมด (<?php echo count($students); ?> คน)
                            </span>
                            <input type="text" id="searchInput" class="form-control form-control-sm w-auto" placeholder="ค้นหานิสิต...">
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>รหัสนิสิต</th>
                                            <th>ชื่อ-นามสกุล</th>
                                            <th>หลักสูตร</th>
                                            <th>ภาควิชา</th>
                                            <th>จังหวัด</th>
                                            <th>การกระทำ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($students)): ?>
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                                    <p class="text-muted">ไม่พบข้อมูลนิสิต</p>
                                                </td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($students as $student): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($student['s_id'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($student['s_name'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($student['หลักสูตร'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($student['ภาควิชา'] ?? ''); ?></td>
                                                    <td><?php echo htmlspecialchars($student['จังหวัด'] ?? ''); ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <button class="btn btn-sm btn-warning" 
                                                                    data-bs-toggle="modal" 
                                                                    data-bs-target="#editStudentModal"
                                                                    onclick="editStudent(
                                                                        '<?php echo $student['s_id']; ?>',
                                                                        '<?php echo addslashes($student['s_name'] ?? ''); ?>',
                                                                        '<?php echo addslashes($student['หลักสูตร'] ?? ''); ?>',
                                                                        '<?php echo addslashes($student['คณะ'] ?? ''); ?>',
                                                                        '<?php echo addslashes($student['ภาควิชา'] ?? ''); ?>',
                                                                        '<?php echo addslashes($student['จบจากโรงเรียน'] ?? ''); ?>',
                                                                        '<?php echo $student['lat'] ?? ''; ?>',
                                                                        '<?php echo $student['long'] ?? ''; ?>',
                                                                        '<?php echo addslashes($student['ตำบล'] ?? ''); ?>',
                                                                        '<?php echo addslashes($student['อำเภอ'] ?? ''); ?>',
                                                                        '<?php echo addslashes($student['จังหวัด'] ?? ''); ?>'
                                                                    )">
                                                                <i class="fas fa-edit"></i>
                                                            </button>
                                                            <form method="POST" style="display: inline;" onsubmit="return confirm('แน่ใจว่าต้องการลบข้อมูลนี้?')">
                                                                <input type="hidden" name="action" value="delete">
                                                                <input type="hidden" name="table" value="<?php echo $current_table; ?>">
                                                                <input type="hidden" name="student_id" value="<?php echo $student['s_id']; ?>">
                                                                <button type="submit" class="btn btn-sm btn-danger">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Add Point Modal -->
    <div class="modal fade" id="addPointModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มจุดใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">ละติจูด</label>
                        <input type="text" class="form-control" id="new-lat" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลองจิจูด</label>
                        <input type="text" class="form-control" id="new-long" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">เลือกชั้นปี</label>
                        <select class="form-select" id="new-grade">
                            <option value="agi64">AGI64</option>
                            <option value="agi65">AGI65</option>
                            <option value="agi66">AGI66</option>
                            <option value="agi67">AGI67</option>
                        </select>
                    </div>
                    <div class="alert alert-info">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            หลังจากเพิ่มจุดแล้ว ให้ไปที่หน้าจัดการข้อมูลของชั้นปีที่เลือกเพื่อเพิ่มข้อมูลนิสิต
                        </small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-primary" onclick="saveNewPoint()">บันทึกจุด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Point Modal -->
    <div class="modal fade" id="editPointModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขจุด</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="point-info">
                        <!-- ข้อมูลจุดจะถูกเติมโดย JavaScript -->
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ละติจูด</label>
                        <input type="number" step="any" class="form-control" id="edit-point-lat">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">ลองจิจูด</label>
                        <input type="number" step="any" class="form-control" id="edit-point-long">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn btn-warning" onclick="deletePoint()">ลบจุด</button>
                    <button type="button" class="btn btn-primary" onclick="updatePoint()">อัพเดทจุด</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Student Modal -->
    <div class="modal fade" id="addStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">เพิ่มข้อมูลนิสิตใหม่</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <input type="hidden" name="table" value="<?php echo $current_table; ?>">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">รหัสนิสิต *</label>
                                    <input type="text" class="form-control" name="s_id" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ชื่อ-นามสกุล *</label>
                                    <input type="text" class="form-control" name="s_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">หลักสูตร</label>
                                    <input type="text" class="form-control" name="หลักสูตร">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">คณะ</label>
                                    <input type="text" class="form-control" name="คณะ">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ภาควิชา</label>
                                    <input type="text" class="form-control" name="ภาควิชา">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">โรงเรียนที่จบ</label>
                                    <input type="text" class="form-control" name="จบจากโรงเรียน">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ละติจูด</label>
                                    <input type="number" step="any" class="form-control" name="lat">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">ลองจิจูด</label>
                                    <input type="number" step="any" class="form-control" name="long">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">ตำบล</label>
                                    <input type="text" class="form-control" name="ตำบล">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">อำเภอ</label>
                                    <input type="text" class="form-control" name="อำเภอ">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">จังหวัด</label>
                                    <input type="text" class="form-control" name="จังหวัด">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Student Modal -->
    <div class="modal fade" id="editStudentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">แก้ไขข้อมูลนิสิต</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="table" value="<?php echo $current_table; ?>">
                        <input type="hidden" name="student_id" id="edit_student_id">
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ชื่อ-นามสกุล *</label>
                                    <input type="text" class="form-control" name="s_name" id="edit_s_name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">หลักสูตร</label>
                                    <input type="text" class="form-control" name="หลักสูตร" id="edit_หลักสูตร">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">คณะ</label>
                                    <input type="text" class="form-control" name="คณะ" id="edit_คณะ">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">ภาควิชา</label>
                                    <input type="text" class="form-control" name="ภาควิชา" id="edit_ภาควิชา">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">โรงเรียนที่จบ</label>
                                    <input type="text" class="form-control" name="จบจากโรงเรียน" id="edit_จบจากโรงเรียน">
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">ละติจูด</label>
                                            <input type="number" step="any" class="form-control" name="lat" id="edit_lat">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">ลองจิจูด</label>
                                            <input type="number" step="any" class="form-control" name="long" id="edit_long">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">ตำบล</label>
                                    <input type="text" class="form-control" name="ตำบล" id="edit_ตำบล">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">อำเภอ</label>
                                    <input type="text" class="form-control" name="อำเภอ" id="edit_อำเภอ">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">จังหวัด</label>
                                    <input type="text" class="form-control" name="จังหวัด" id="edit_จังหวัด">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn btn-primary">อัพเดทข้อมูล</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        // Search functionality
        document.getElementById('searchInput')?.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });

        // Edit student function
        function editStudent(s_id, s_name, หลักสูตร, คณะ, ภาควิชา, จบจากโรงเรียน, lat, long, ตำบล, อำเภอ, จังหวัด) {
            document.getElementById('edit_student_id').value = s_id;
            document.getElementById('edit_s_name').value = s_name || '';
            document.getElementById('edit_หลักสูตร').value = หลักสูตร || '';
            document.getElementById('edit_คณะ').value = คณะ || '';
            document.getElementById('edit_ภาควิชา').value = ภาควิชา || '';
            document.getElementById('edit_จบจากโรงเรียน').value = จบจากโรงเรียน || '';
            document.getElementById('edit_lat').value = lat || '';
            document.getElementById('edit_long').value = long || '';
            document.getElementById('edit_ตำบล').value = ตำบล || '';
            document.getElementById('edit_อำเภอ').value = อำเภอ || '';
            document.getElementById('edit_จังหวัด').value = จังหวัด || '';
        }

        // Map functionality for overview page
        <?php if ($is_overview): ?>
        let map = null;
        let markers = [];
        let tempMarker = null;
        let isAddMode = false;
        let currentEditingStudent = null;
        const allMapData = <?php echo json_encode($all_map_data); ?>;

        function initMap() {
            map = L.map('overview-map').setView([13.736717, 100.523186], 6);
            
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            // Add double click event for adding points
            map.on('dblclick', function(e) {
                if (isAddMode) {
                    addTempMarker(e.latlng);
                    showAddPointModal(e.latlng);
                }
            });

            // Add click event for existing markers
            map.on('click', function(e) {
                if (isAddMode && tempMarker) {
                    map.removeLayer(tempMarker);
                    tempMarker = null;
                }
            });

            loadMapData('all');
            setupMapControls();
        }

        function setupMapControls() {
            // Zoom in
            document.getElementById('zoom-in').addEventListener('click', function() {
                map.zoomIn();
            });

            // Zoom out
            document.getElementById('zoom-out').addEventListener('click', function() {
                map.zoomOut();
            });

            // Locate Thailand
            document.getElementById('locate-th').addEventListener('click', function() {
                map.setView([13.736717, 100.523186], 6);
            });

            // Add point mode toggle
            document.getElementById('add-point-mode').addEventListener('click', function() {
                isAddMode = !isAddMode;
                this.classList.toggle('btn-success');
                this.classList.toggle('btn-outline-success');
                
                if (isAddMode) {
                    this.innerHTML = '<i class="fas fa-times me-1"></i>ยกเลิกโหมด';
                    showMessage('โหมดเพิ่มจุด: ดับเบิลคลิกบนแผนที่เพื่อเพิ่มจุด', 'info');
                } else {
                    this.innerHTML = '<i class="fas fa-plus me-1"></i>โหมดเพิ่มจุด';
                    if (tempMarker) {
                        map.removeLayer(tempMarker);
                        tempMarker = null;
                    }
                }
            });
        }

        function addTempMarker(latlng) {
            if (tempMarker) {
                map.removeLayer(tempMarker);
            }
            
            tempMarker = L.circleMarker(latlng, {
                radius: 10,
                fillColor: '#9b59b6',
                color: '#fff',
                weight: 3,
                opacity: 1,
                fillOpacity: 0.8,
                className: 'temp-marker'
            }).addTo(map);
        }

        function showAddPointModal(latlng) {
            document.getElementById('new-lat').value = latlng.lat.toFixed(6);
            document.getElementById('new-long').value = latlng.lng.toFixed(6);
            const modal = new bootstrap.Modal(document.getElementById('addPointModal'));
            modal.show();
        }

        function saveNewPoint() {
            const lat = document.getElementById('new-lat').value;
            const lng = document.getElementById('new-long').value;
            const grade = document.getElementById('new-grade').value;
            
            showMessage(`เพิ่มจุดที่ละติจูด: ${lat}, ลองจิจูด: ${lng} สำหรับชั้นปี ${grade.toUpperCase()} สำเร็จ!`, 'success');
            
            setTimeout(() => {
                window.location.href = `admin.php?table=${grade}`;
            }, 2000);
            
            bootstrap.Modal.getInstance(document.getElementById('addPointModal')).hide();
            
            if (tempMarker) {
                map.removeLayer(tempMarker);
                tempMarker = null;
            }
            
            isAddMode = false;
            document.getElementById('add-point-mode').classList.remove('btn-success');
            document.getElementById('add-point-mode').classList.add('btn-outline-success');
            document.getElementById('add-point-mode').innerHTML = '<i class="fas fa-plus me-1"></i>โหมดเพิ่มจุด';
        }

        function showEditPointModal(student) {
            currentEditingStudent = student;
            
            document.getElementById('point-info').innerHTML = `
                <div class="mb-3 p-3 bg-light rounded">
                    <h6>ข้อมูลนิสิต</h6>
                    <p class="mb-1"><strong>ชื่อ:</strong> ${student.s_name || '-'}</p>
                    <p class="mb-1"><strong>รหัส:</strong> ${student.s_id || '-'}</p>
                    <p class="mb-1"><strong>ชั้นปี:</strong> ${student.table_name ? student.table_name.toUpperCase() : '-'}</p>
                    <p class="mb-0"><strong>หลักสูตร:</strong> ${student.หลักสูตร || '-'}</p>
                </div>
            `;
            
            document.getElementById('edit-point-lat').value = student.lat;
            document.getElementById('edit-point-long').value = student.long;
            
            const modal = new bootstrap.Modal(document.getElementById('editPointModal'));
            modal.show();
        }

        function updatePoint() {
            if (!currentEditingStudent) return;
            
            const newLat = parseFloat(document.getElementById('edit-point-lat').value);
            const newLong = parseFloat(document.getElementById('edit-point-long').value);
            
            if (isNaN(newLat) || isNaN(newLong)) {
                showMessage('กรุณากรอกค่าพิกัดให้ถูกต้อง', 'danger');
                return;
            }
            
            updateStudentCoordinates(currentEditingStudent, newLat, newLong);
        }

        function deletePoint() {
            if (!currentEditingStudent) return;
            
            if (confirm(`แน่ใจว่าต้องการลบจุดของ "${currentEditingStudent.s_name || 'นิสิต'}" นี้?`)) {
                deleteStudentCoordinates(currentEditingStudent);
            }
        }

        function updateStudentCoordinates(student, newLat, newLong) {
            const formData = new FormData();
            formData.append('action', 'update_coordinates');
            formData.append('table', student.table_name);
            formData.append('student_id', student.s_id);
            formData.append('lat', newLat);
            formData.append('long', newLong);
            formData.append('ajax', 'true');
            
            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateMarkerPosition(student, newLat, newLong);
                    showMessage(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editPointModal')).hide();
                } else {
                    showMessage(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('เกิดข้อผิดพลาดในการอัพเดทพิกัด', 'danger');
            });
        }

        function deleteStudentCoordinates(student) {
            const formData = new FormData();
            formData.append('action', 'delete_coordinates');
            formData.append('table', student.table_name);
            formData.append('student_id', student.s_id);
            formData.append('ajax', 'true');
            
            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    removeMarker(student);
                    showMessage(data.message, 'success');
                    bootstrap.Modal.getInstance(document.getElementById('editPointModal')).hide();
                } else {
                    showMessage(data.message, 'danger');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showMessage('เกิดข้อผิดพลาดในการลบจุด', 'danger');
            });
        }

        function updateMarkerPosition(student, newLat, newLong) {
            markers.forEach(marker => {
                if (marker.studentData && marker.studentData.s_id === student.s_id && marker.studentData.table_name === student.table_name) {
                    marker.setLatLng([newLat, newLong]);
                    marker.studentData.lat = newLat;
                    marker.studentData.long = newLong;
                    
                    marker.bindPopup(createPopupContent(marker.studentData));
                }
            });
        }

        function removeMarker(student) {
            markers.forEach((marker, index) => {
                if (marker.studentData && marker.studentData.s_id === student.s_id && marker.studentData.table_name === student.table_name) {
                    map.removeLayer(marker);
                    markers.splice(index, 1);
                }
            });
        }

        function createPopupContent(student) {
            const studentJson = JSON.stringify(student).replace(/"/g, '&quot;');
            return `
                <div style="min-width: 200px;">
                    <h6 style="margin: 0 0 5px 0; color: #2c3e50;">${student.s_name || ''}</h6>
                    <p style="margin: 2px 0; font-size: 12px;"><strong>รหัส:</strong> ${student.s_id || ''}</p>
                    <p style="margin: 2px 0; font-size: 12px;"><strong>ชั้นปี:</strong> ${student.table_name ? student.table_name.toUpperCase() : ''}</p>
                    <p style="margin: 2px 0; font-size: 12px;"><strong>หลักสูตร:</strong> ${student.หลักสูตร || ''}</p>
                    <p style="margin: 2px 0; font-size: 12px;"><strong>จังหวัด:</strong> ${student.จังหวัด || ''}</p>
                    <div class="mt-2">
                        <button class="btn btn-sm btn-warning w-100 popup-edit-btn" onclick="window.showEditPointModal(${studentJson})">
                            <i class="fas fa-edit me-1"></i>แก้ไขจุด
                        </button>
                    </div>
                </div>
            `;
        }

        window.showEditPointModal = showEditPointModal;

        function loadMapData(grade) {
            markers.forEach(marker => map.removeLayer(marker));
            markers = [];

            let filteredData = allMapData;
            if (grade !== 'all') {
                filteredData = allMapData.filter(student => student.table_name === grade);
            }

            filteredData.forEach(student => {
                if (student.lat && student.long && student.lat != 0 && student.long != 0) {
                    const gradeColor = getGradeColor(student.table_name);
                    const marker = L.circleMarker([student.lat, student.long], {
                        radius: 8,
                        fillColor: gradeColor,
                        color: '#fff',
                        weight: 2,
                        opacity: 1,
                        fillOpacity: 0.8
                    }).addTo(map);

                    marker.studentData = student;
                    marker.bindPopup(createPopupContent(student));
                    markers.push(marker);
                }
            });

            if (markers.length > 0) {
                const group = new L.featureGroup(markers);
                map.fitBounds(group.getBounds().pad(0.1));
            } else {
                map.setView([13.736717, 100.523186], 6);
            }
        }

        function getGradeColor(grade) {
            const colors = {
                'agi64': '#e74c3c',
                'agi65': '#3498db',
                'agi66': '#2ecc71',
                'agi67': '#f39c12'
            };
            return colors[grade] || '#95a5a6';
        }

        function showMessage(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 80px; right: 20px; z-index: 2000; min-width: 300px;';
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', function() {
            initMap();
            
            document.getElementById('refresh-map')?.addEventListener('click', function() {
                const selectedGrade = document.getElementById('map-grade-select').value;
                loadMapData(selectedGrade);
            });

            document.getElementById('map-grade-select')?.addEventListener('change', function() {
                const selectedGrade = this.value;
                loadMapData(selectedGrade);
            });
        });
        <?php endif; ?>
    </script>
</body>
</html>