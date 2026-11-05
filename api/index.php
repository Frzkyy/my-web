
<?php

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");

$allowedOrigins = array_filter([
    getenv("FRONTEND_URL"),
    "http://localhost:5500",
    "http://127.0.0.1:5500"
]);

$origin = $_SERVER["HTTP_ORIGIN"] ?? "";

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Vary: Origin");
}

if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(204);
    exit;
}

require_once __DIR__ . "/koneksi.php";

function response($data, $status = 200) {
    http_response_code($status);
    echo json_encode($data);
    exit;
}

try {

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS mahasiswa (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nama VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");

    $method = $_SERVER["REQUEST_METHOD"];

    $input = json_decode(
        file_get_contents("php://input"),
        true
    ) ?? [];

    switch ($method) {

        case "GET":

            $stmt = $pdo->query(
                "SELECT * FROM mahasiswa ORDER BY id DESC"
            );

            response($stmt->fetchAll());

            break;

        case "POST":

            $nama = trim($input["nama"] ?? "");

            if ($nama === "") {
                response(["error" => "Nama wajib diisi"], 400);
            }

            if (strlen($nama) > 100) {
                response(["error" => "Nama maksimal 100 karakter"], 400);
            }

            $stmt = $pdo->prepare(
                "INSERT INTO mahasiswa (nama) VALUES (?)"
            );

            $stmt->execute([$nama]);

            response([
                "message" => "Data berhasil ditambahkan",
                "id" => $pdo->lastInsertId()
            ], 201);

            break;

        case "PUT":

            $id = filter_var(
                $input["id"] ?? null,
                FILTER_VALIDATE_INT
            );

            $nama = trim($input["nama"] ?? "");

            if (!$id || $nama === "") {
                response(["error" => "ID dan nama wajib diisi"], 400);
            }

            if (strlen($nama) > 100) {
                response(["error" => "Nama maksimal 100 karakter"], 400);
            }

            $check = $pdo->prepare(
                "SELECT id FROM mahasiswa WHERE id = ?"
            );

            $check->execute([$id]);

            if (!$check->fetch()) {
                response(["error" => "Data tidak ditemukan"], 404);
            }

            $stmt = $pdo->prepare(
                "UPDATE mahasiswa SET nama = ? WHERE id = ?"
            );

            $stmt->execute([$nama, $id]);

            response(["message" => "Data berhasil diubah"]);

            break;

        case "DELETE":

            $id = filter_var(
                $input["id"] ?? null,
                FILTER_VALIDATE_INT
            );

            if (!$id) {
                response(["error" => "ID tidak valid"], 400);
            }

            $stmt = $pdo->prepare(
                "DELETE FROM mahasiswa WHERE id = ?"
            );

            $stmt->execute([$id]);

            if ($stmt->rowCount() === 0) {
                response(["error" => "Data tidak ditemukan"], 404);
            }

            response(["message" => "Data berhasil dihapus"]);

            break;

        default:

            response(["error" => "Method tidak didukung"], 405);
    }

} catch (Throwable $e) {

    error_log($e->getMessage());

    response(["error" => "Terjadi kesalahan pada server"], 500);
}
