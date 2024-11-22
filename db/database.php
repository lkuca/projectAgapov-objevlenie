<?php
$config = require __DIR__ . '/../config/config.php';

function getDatabaseConnection() {
    $config = require __DIR__ . '/../config/config.php';
    $dsn = "mysql:host={$config['db']['host']};dbname={$config['db']['dbname']};charset=utf8";
    try {
        return new PDO($dsn, $config['db']['user'], $config['db']['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e) {
        die('Database connection error: ' . $e->getMessage());
    }
}