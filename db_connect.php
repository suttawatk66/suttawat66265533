<?php
// db_connect.php
$config = require 'db_config.php';

function db_connect() {
    global $config;
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']};";
    try {
        $pdo = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // ไม่ใช้ die() เพราะจะส่ง HTML กลับ
        throw new PDOException("DB connection failed: " . $e->getMessage());
    }
}
?>