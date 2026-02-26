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

// Validate required field: nama_lengkap
if (!isset($inputData['nama_lengkap']) || empty(trim($inputData['nama_lengkap']))) {
    $response = [
        "success" => false,
        "message" => "nama_lengkap wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

// Validate required field: wa
if (!isset($inputData['wa']) || empty(trim($inputData['wa']))) {
    $response = [
        "success" => false,
        "message" => "wa wajib diisi"
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

$nama_lengkap = trim($inputData['nama_lengkap']);
$email = isset($inputData['email']) ? trim($inputData['email']) : '';
$wa = trim($inputData['wa']);
$password = trim($inputData['password']);
$kota = isset($inputData['kota']) ? trim($inputData['kota']) : '';

// Validate password minimum length
if (strlen($password) < 8) {
    $response = [
        "success" => false,
        "message" => "password minimal 8 karakter"
    ];
    echo json_encode($response);
    exit;
}

// Check for duplicate email (only if email is provided)
if (!empty($email)) {
    $checkEmailQuery = "SELECT id_guest FROM guest WHERE email = ?";
    $stmt = $conn->prepare($checkEmailQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = [
            "success" => false,
            "message" => "email sudah ada, tidak boleh duplikat"
        ];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();
}

// Check for duplicate wa
$checkWaQuery = "SELECT id_guest FROM guest WHERE wa = ?";
$stmt = $conn->prepare($checkWaQuery);
$stmt->bind_param("s", $wa);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response = [
        "success" => false,
        "message" => "nomor WhatsApp sudah ada, tidak boleh duplikat"
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
$total_point = 0.00; // Default 0, tidak perlu dibawa dari input

// Prepare insert query (exclude id_guest, created_at, last_login)
// Email is optional, set to NULL if empty
$query = "INSERT INTO guest (nama_lengkap, email, wa, password, kota, aktif, total_point) VALUES (?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$emailValue = !empty($email) ? $email : null;
$stmt->bind_param("sssssdi", $nama_lengkap, $emailValue, $wa, $hashedPassword, $kota, $total_point, $aktif);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_guest" => $newId,
        "nama_lengkap" => $nama_lengkap,
        "email" => $email,
        "wa" => $wa,
        "kota" => $kota,
        "aktif" => $aktif,
        "total_point" => $total_point
    ];

    $response = [
        "success" => true,
        "message" => "Data guest berhasil ditambahkan",
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
