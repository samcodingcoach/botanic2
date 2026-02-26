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

    if (json_last_error() !== JSON_ERROR_NONE) {
        $inputData = $_POST;
    }
} else {
    $inputData = $_POST;
}

// Validate required field: id_guest
if (!isset($inputData['id_guest']) || empty($inputData['id_guest'])) {
    $response = [
        "success" => false,
        "message" => "id_guest wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

// Validate required field: password (for verification)
if (!isset($inputData['password']) || empty(trim($inputData['password']))) {
    $response = [
        "success" => false,
        "message" => "password wajib diisi untuk verifikasi"
    ];
    echo json_encode($response);
    exit;
}

$id_guest = (int) $inputData['id_guest'];
$inputPassword = trim($inputData['password']);

// Check if id_guest exists and verify password
$checkQuery = "SELECT id_guest, password FROM guest WHERE id_guest = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_guest);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data guest tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$guestData = $result->fetch_assoc();

// Verify password matches using password_verify()
if (!password_verify($inputPassword, $guestData['password'])) {
    $response = [
        "success" => false,
        "message" => "password tidak sesuai"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$stmt->close();

// Perform delete
$deleteQuery = "DELETE FROM guest WHERE id_guest = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id_guest);

if ($stmt->execute()) {
    $response = [
        "success" => true,
        "message" => "Data guest berhasil dihapus"
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
