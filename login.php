<?php
session_start();

// ข้อมูลฐานข้อมูล
$host = "localhost";
$port = "5432";
$dbname = "webgis";
$user = "postgres";
$password = "postgres";

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password_in = $_POST['password'] ?? '';

    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;";
        $pdo = new PDO($dsn, $user, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $stmt = $pdo->prepare('SELECT * FROM users WHERE username = :u');
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password_in, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'username' => $user['username'],
                'display_name' => $user['display_name']
            ];
            header("Location: admin.php");
            exit;
        } else {
            $message = '❌ Username หรือ Password ไม่ถูกต้อง';
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบจัดการข้อมูลนิสิต</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.3);
            overflow: hidden;
        }
        .login-header {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }
        .login-body {
            padding: 40px;
        }
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 16px;
        }
        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="login-container">
                    <div class="login-header">
                        <h2><i class="fas fa-database me-2"></i>ระบบจัดการข้อมูลนิสิต</h2>
                        <p class="mb-0 mt-2">กรุณาเข้าสู่ระบบเพื่อจัดการข้อมูล</p>
                    </div>
                    <div class="login-body">
                        <?php if ($message): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?php echo $message; ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-4">
                                <label class="form-label fw-bold">ชื่อผู้ใช้</label>
                                <input type="text" class="form-control" name="username" required 
                                       placeholder="กรอกชื่อผู้ใช้">
                            </div>
                            <div class="mb-4">
                                <label class="form-label fw-bold">รหัสผ่าน</label>
                                <input type="password" class="form-control" name="password" required 
                                       placeholder="กรอกรหัสผ่าน">
                            </div>
                            <button type="submit" class="btn btn-primary w-100 py-2 fs-5">
                                <i class="fas fa-sign-in-alt me-2"></i>เข้าสู่ระบบ
                            </button>
                        </form>
                        
                        <div class="text-center mt-4">
                            <a href="student.html" class="text-decoration-none">
                                <i class="fas fa-arrow-left me-1"></i>กลับไปหน้าหลัก
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>