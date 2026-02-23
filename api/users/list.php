<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once '../../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Method not allowed"]);
    exit();
}

try {
    $query = "SELECT 
        id_users,
        username,
        aktif,
        created_at,
        last_login
    FROM users ORDER BY id_users DESC";

    $result = $conn->query($query);

    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }

    $users = [];
    while ($row = $result->fetch_assoc()) {
        $users[] = [
            "id_users" => (int)$row["id_users"],
            "username" => $row["username"],
            "aktif" => (int)$row["aktif"],
            "created_at" => $row["created_at"],
            "last_login" => $row["last_login"]
        ];
    }

    echo json_encode([
        "success" => true,
        "data" => $users
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}

$conn->close();
?>
