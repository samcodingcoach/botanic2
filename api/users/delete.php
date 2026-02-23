<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = [
        "success" => false,
        "message" => "Method not allowed. Use POST method."
    ];
    echo json_encode($response);
    exit;
}

// Support both form-data and raw JSON body
$inputData = [];
$contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : (isset($_SERVER['HTTP_CONTENT_TYPE']) ? $_SERVER['HTTP_CONTENT_TYPE'] : '');

if (strpos($contentType, 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);

    // Fallback to POST if decode fails
    if (json_last_error() !== JSON_ERROR_NONE) {
        $inputData = $_POST;
    }
} else {
    $inputData = $_POST;
}

// Validate required field: id_users
if (!isset($inputData['id_users']) || empty($inputData['id_users'])) {
    $response = [
        "success" => false,
        "message" => "id_users wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_users = (int) $inputData['id_users'];

// Check total users count (cannot delete if only 1 user remains)
$countQuery = "SELECT COUNT(*) as total FROM users";
$countResult = $conn->query($countQuery);
$countData = $countResult->fetch_assoc();
$totalUsers = (int) $countData['total'];

if ($totalUsers <= 1) {
    $response = [
        "success" => false,
        "message" => "Tidak dapat menghapus user. Minimal harus ada 1 user di sistem."
    ];
    echo json_encode($response);
    exit;
}

// Check if id_users exists
$checkQuery = "SELECT id_users, username FROM users WHERE id_users = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_users);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data user tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$deletedData = $result->fetch_assoc();
$stmt->close();

// Perform delete
$deleteQuery = "DELETE FROM users WHERE id_users = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id_users);

if ($stmt->execute()) {
    $response = [
        "success" => true,
        "message" => "Data user berhasil dihapus",
        "data" => [
            "id_users" => $id_users,
            "username" => $deletedData['username']
        ]
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal menghapus data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
