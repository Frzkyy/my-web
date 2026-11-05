
<?php

$host = getenv("DB_HOST");
$port = getenv("DB_PORT") ?: 3306;
$db = getenv("DB_NAME");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");

try {
    $pdo = new PDO(
        "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4",
        $user,
        $pass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

} catch (PDOException $e) {
    error_log($e->getMessage());

    http_response_code(500);
    header("Content-Type: application/json");

    echo json_encode([
        "error" => "Koneksi database gagal"
    ]);

    exit;
}
