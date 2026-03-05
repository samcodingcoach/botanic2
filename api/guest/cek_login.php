<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Start session for server-side authentication
session_start();

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

// Validate required fields
if (!isset($inputData['username']) || empty(trim($inputData['username']))) {
    $response = [
        "success" => false,
        "message" => "username wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

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

// Check guest credentials (email or wa)
$query = "SELECT g.id_guest, g.email, g.wa, g.nama_lengkap, g.`password` FROM guest g WHERE g.aktif = 1 AND (g.email = ? OR g.wa = ?)";
$stmt = $conn->prepare($query);
$stmt->bind_param("ss", $username, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "email/wa tidak ditemukan atau guest tidak aktif"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$guest = $result->fetch_assoc();

// Verify password using password_verify()
if (!password_verify($password, $guest['password'])) {
    $response = [
        "success" => false,
        "message" => "password salah"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$stmt->close();

// Update last_login
$updateQuery = "UPDATE guest SET last_login = NOW() WHERE id_guest = ?";
$updateStmt = $conn->prepare($updateQuery);
$updateStmt->bind_param("i", $guest['id_guest']);
$updateStmt->execute();
$updateStmt->close();

// Login successful
$response = [
    "success" => true,
    "message" => "Login berhasil",
    "data" => [
        "id_guest" => (int) $guest['id_guest'],
        "nama_lengkap" => $guest['nama_lengkap'],
        "email" => $guest['email'],
        "wa" => $guest['wa']
    ]
];

// Set server-side session
$_SESSION['id_guest'] = $guest['id_guest'];
$_SESSION['nama_lengkap'] = $guest['nama_lengkap'];
$_SESSION['email'] = $guest['email'];
$_SESSION['wa'] = $guest['wa'];

echo json_encode($response);

$conn->close();
?>
