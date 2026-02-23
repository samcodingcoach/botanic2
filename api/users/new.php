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
    
    // Debug: log raw input if decode fails
    if (json_last_error() !== JSON_ERROR_NONE) {
        $inputData = $_POST;
    }
} else {
    $inputData = $_POST;
}

// Validate required field: username
if (!isset($inputData['username']) || empty(trim($inputData['username']))) {
    $response = [
        "success" => false,
        "message" => "username wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

// Validate required field: password
if (!isset($inputData['password']) || empty(trim($inputData['password']))) {
    $response = [
        "success" => false,
        "message" => "password wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$username = trim($inputData['username']);
$password = trim($inputData['password']);

// Validate password minimum length
if (strlen($password) < 8) {
    $response = [
        "success" => false,
        "message" => "password minimal 8 karakter"
    ];
    echo json_encode($response);
    exit;
}

// Check for duplicate username
$checkQuery = "SELECT id_users FROM users WHERE username = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response = [
        "success" => false,
        "message" => "username sudah ada, tidak boleh duplikat"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Hash password using password_hash()
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

// Set default values for optional fields
$aktif = isset($inputData['aktif']) ? (int)$inputData['aktif'] : 1;

// Prepare insert query (exclude id_users, created_at, last_login)
$query = "INSERT INTO users (username, password, aktif) VALUES (?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("ssi", $username, $hashedPassword, $aktif);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_users" => $newId,
        "username" => $username,
        "aktif" => $aktif
    ];

    $response = [
        "success" => true,
        "message" => "Data user berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal menyimpan data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
