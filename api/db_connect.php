<?php
// api/db_connect.php

// ตรวจสอบว่ามีไฟล์ config หรือไม่
if (!file_exists(__DIR__ . '/db_config.php')) {
    throw new Exception('Database configuration file not found in: ' . __DIR__);
}

$config = require __DIR__ . '/db_config.php';

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
        throw new PDOException("DB connection failed: " . $e->getMessage());
    }
}
?>