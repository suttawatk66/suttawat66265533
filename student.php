<?php
// stu.php
// Dynamic CRUD for PostgreSQL tables: agi64, agi65, agi66, agi67
// Place this file in your web server root (e.g., /var/www/html/) and open in browser.

require_once 'db_connect.php'; // ใช้ไฟล์เชื่อมต่อฐานข้อมูลกลาง
$tables = ['agi64', 'agi65', 'agi66', 'agi67'];

// เรียกใช้งานการเชื่อมต่อฐานข้อมูล
$pdo = db_connect();


// Utility: get columns for a table (excluding system cols)
function get_columns($pdo, $table) {
    $sql = "SELECT column_name, data_type
            FROM information_schema.columns
            WHERE table_schema = 'public' AND table_name = :table
            ORDER BY ordinal_position";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['table' => $table]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Utility: find primary key column(s)
function get_primary_keys($pdo, $table) {
    $sql = "SELECT kcu.column_name
            FROM information_schema.table_constraints tc
            JOIN information_schema.key_column_usage kcu
              ON tc.constraint_name = kcu.constraint_name
             AND tc.table_schema = kcu.table_schema
            WHERE tc.constraint_type = 'PRIMARY KEY'
              AND tc.table_name = :table
              AND tc.table_schema = 'public'
            ORDER BY kcu.ordinal_position";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['table' => $table]);
    return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'column_name');
}

// Basic CSRF token
session_start();
if (!isset($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(16));
$token = $_SESSION['_csrf'];

$pdo = db_connect();

// Router-ish handling via GET/POST params: table, action, pk (encoded JSON for composite keys)
$table = $_GET['table'] ?? null;
action = $_REQUEST['action'] ?? null; // create|edit|delete|view

// Sanitize requested table
if ($table && !in_array($table, $tables)) {
    $table = null;
}

// Helper to build WHERE clause for primary key(s) from request
function build_pk_where($pk_cols, $input, &$params) {
    $where = [];
    foreach ($pk_cols as $col) {
        if (!isset($input[$col])) throw new Exception("Missing PK column: $col");
        $where[] = "\"$col\" = :pk_$col";
        $params[":pk_$col"] = $input[$col];
    }
    return implode(' AND ', $where);
}

// Handle POST actions
$message = '';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['_csrf'], $_POST['_csrf'] ?? '')) {
            throw new Exception('Invalid CSRF token');
        }
        $table = $_POST['table'] ?? null;
        if (!$table || !in_array($table, $tables)) throw new Exception('Invalid table');
        $pk_cols = get_primary_keys($pdo, $table);
        if ($_POST['action'] === 'create') {
            // Collect posted fields based on table columns
            $cols = get_columns($pdo, $table);
            $insert_cols = [];
            $placeholders = [];
            $params = [];
            foreach ($cols as $c) {
                $name = $c['column_name'];
                if (isset($_POST[$name]) && $_POST[$name] !== '') {
                    $insert_cols[] = '"' . $name . '"';
                    $placeholders[] = ':' . $name;
                    $params[':' . $name] = $_POST[$name];
                }
            }
            if (count($insert_cols) === 0) throw new Exception('No fields to insert');
            $sql = "INSERT INTO \"$table\" (" . implode(',', $insert_cols) . ") VALUES (" . implode(',', $placeholders) . ")";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = 'Record created successfully.';
        } elseif ($_POST['action'] === 'edit') {
            $cols = get_columns($pdo, $table);
            $set = [];
            $params = [];
            foreach ($cols as $c) {
                $name = $c['column_name'];
                if (array_key_exists($name, $_POST) && !in_array($name, $pk_cols)) {
                    $set[] = '"' . $name . '" = :' . $name;
                    $params[':' . $name] = $_POST[$name];
                }
            }
            if (count($set) === 0) throw new Exception('No fields to update');
            $where = build_pk_where($pk_cols, $_POST, $params);
            $sql = "UPDATE \"$table\" SET " . implode(', ', $set) . " WHERE " . $where;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = 'Record updated successfully.';
        } elseif ($_POST['action'] === 'delete') {
            $pk_cols = get_primary_keys($pdo, $table);
            $params = [];
            $where = build_pk_where($pk_cols, $_POST, $params);
            $sql = "DELETE FROM \"$table\" WHERE " . $where;
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $message = 'Record deleted successfully.';
        }
    }
} catch (Exception $e) {
    $message = 'Error: ' . $e->getMessage();
}

// Fetch data for listing
function fetch_table_rows($pdo, $table, $limit = 200) {
    $sql = "SELECT * FROM \"$table\" LIMIT :lim";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':lim', (int)$limit, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// HTML output starts here
?><!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>WebGIS CRUD - Dynamic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-4">
    <h1 class="mb-3">WebGIS CRUD (agi64–agi67)</h1>
    <?php if ($message): ?>
        <div class="alert alert-info"><?php echo htmlspecialchars($message); ?></div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-3">
            <div class="list-group">
                <a href="?" class="list-group-item list-group-item-action">หน้าแรก</a>
                <?php foreach ($tables as $t): ?>
                    <a href="?table=<?php echo urlencode($t); ?>" class="list-group-item list-group-item-action <?php echo ($table===$t)?'active':''; ?>"><?php echo $t; ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div class="col-md-9">
            <?php if (!$table): ?>
                <p>เลือกตารางทางซ้ายเพื่อดูข้อมูล (รองรับ: agi64, agi65, agi66, agi67). ระบบนี้อ่านโครงสร้างอัตโนมัติและสร้างฟอร์ม CRUD แบบไดนามิก</p>
            <?php else: ?>
                <h2>ตาราง: <?php echo htmlspecialchars($table); ?></h2>
                <?php
                $cols = get_columns($pdo, $table);
                $pk_cols = get_primary_keys($pdo, $table);
                $rows = fetch_table_rows($pdo, $table, 500);
                ?>
                <div class="mb-3">
                    <button class="btn btn-success" data-bs-toggle="collapse" data-bs-target="#createForm">เพิ่มระเบียนใหม่</button>
                </div>

                <div class="collapse mb-4" id="createForm">
                    <div class="card card-body">
                        <form method="post">
                            <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($token); ?>">
                            <input type="hidden" name="action" value="create">
                            <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                            <?php foreach ($cols as $c):
                                $name = $c['column_name'];
                                $type = $c['data_type'];
                                // skip serial PK values that are auto-generated? We still show them but optional
                            ?>
                                <div class="mb-2">
                                    <label class="form-label"><?php echo htmlspecialchars($name); ?> <small class="text-muted"><?php echo htmlspecialchars($type); ?></small></label>
                                    <input class="form-control" name="<?php echo htmlspecialchars($name); ?>" placeholder="<?php echo htmlspecialchars($name); ?>">
                                </div>
                            <?php endforeach; ?>
                            <button class="btn btn-primary">บันทึก</button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-striped">
                        <thead>
                            <tr>
                                <?php foreach ($cols as $c): ?>
                                    <th><?php echo htmlspecialchars($c['column_name']); ?></th>
                                <?php endforeach; ?>
                                <th>การกระทำ</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $r): ?>
                            <tr>
                                <?php foreach ($cols as $c): $name = $c['column_name']; ?>
                                    <td style="max-width:240px; word-break:break-word"><?php echo nl2br(htmlspecialchars((string)$r[$name])); ?></td>
                                <?php endforeach; ?>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?php echo md5(json_encode(array_intersect_key($r,array_flip($pk_cols)))); ?>">แก้ไข</button>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#delModal<?php echo md5(json_encode(array_intersect_key($r,array_flip($pk_cols)))); ?>">ลบ</button>
                                    </div>

                                    <!-- Edit Modal -->
                                    <div class="modal fade" id="editModal<?php echo md5(json_encode(array_intersect_key($r,array_flip($pk_cols)))); ?>" tabindex="-1">
                                      <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                          <div class="modal-header">
                                            <h5 class="modal-title">แก้ไข</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                          </div>
                                          <div class="modal-body">
                                            <form method="post">
                                              <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($token); ?>">
                                              <input type="hidden" name="action" value="edit">
                                              <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                                              <?php foreach ($pk_cols as $pk): ?>
                                                <input type="hidden" name="<?php echo htmlspecialchars($pk); ?>" value="<?php echo htmlspecialchars($r[$pk]); ?>">
                                              <?php endforeach; ?>
                                              <?php foreach ($cols as $c): $name = $c['column_name']; $val = $r[$name] ?? ''; ?>
                                                <div class="mb-2">
                                                    <label class="form-label"><?php echo htmlspecialchars($name); ?></label>
                                                    <input class="form-control" name="<?php echo htmlspecialchars($name); ?>" value="<?php echo htmlspecialchars($val); ?>" <?php echo in_array($name,$pk_cols)?'readonly':''; ?>>
                                                </div>
                                              <?php endforeach; ?>
                                              <div class="text-end">
                                                <button class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                                              </div>
                                            </form>
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                    <!-- Delete Modal -->
                                    <div class="modal fade" id="delModal<?php echo md5(json_encode(array_intersect_key($r,array_flip($pk_cols)))); ?>" tabindex="-1">
                                      <div class="modal-dialog">
                                        <div class="modal-content">
                                          <div class="modal-header"><h5 class="modal-title">ยืนยันการลบ</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                                          <div class="modal-body">
                                            <p>ต้องการลบระเบียนนี้จริงหรือไม่?</p>
                                            <form method="post">
                                              <input type="hidden" name="_csrf" value="<?php echo htmlspecialchars($token); ?>">
                                              <input type="hidden" name="action" value="delete">
                                              <input type="hidden" name="table" value="<?php echo htmlspecialchars($table); ?>">
                                              <?php foreach ($pk_cols as $pk): ?>
                                                <input type="hidden" name="<?php echo htmlspecialchars($pk); ?>" value="<?php echo htmlspecialchars($r[$pk]); ?>">
                                              <?php endforeach; ?>
                                              <div class="text-end">
                                                <button type="submit" class="btn btn-danger">ลบ</button>
                                              </div>
                                            </form>
                                          </div>
                                        </div>
                                      </div>
                                    </div>

                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <?php endif; ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
