<?php

$settings = parse_ini_file(__DIR__ . '/../.env');

$pdo = new PDO(
    "mysql:host={$settings['DB_HOST']};dbname={$settings['DB_NAME']};charset=utf8mb4",
    $settings['DB_USER'],
    $settings['DB_PASSWORD'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
