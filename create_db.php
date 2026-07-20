<?php
try {
    $connection = new PDO(
        'mysql:host=127.0.0.1;port=3306',
        'root',
        ''
    );
    $connection->exec('CREATE DATABASE IF NOT EXISTS valuasi_ekonomi CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
    echo "✓ Database created successfully\n";
    exit(0);
} catch (Exception $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    exit(1);
}
