<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, PUT");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// Support both POST and PUT methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'PUT') {
    $response = [
        "success" => false,
        "message" => "Method not allowed. Use POST or PUT method."
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

// Validate required field: id_users
if (!isset($inputData['id_users']) || empty($inputData['id_users'])) {
    $response = [
        "success" => false,
        "message" => "id_users wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

// Validate required field: username (for verification)
if (!isset($inputData['username']) || empty(trim($inputData['username']))) {
    $response = [
        "success" => false,
        "message" => "username wajib diisi"
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

$id_users = (int) $inputData['id_users'];
$inputUsername = trim($inputData['username']);
$inputPassword = trim($inputData['password']);

// Check if id_users exists and verify username and password
$checkQuery = "SELECT id_users, username, password, aktif FROM users WHERE id_users = ?";
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

$oldData = $result->fetch_assoc();

// Verify username matches
if ($oldData['username'] !== $inputUsername) {
    $response = [
        "success" => false,
        "message" => "username tidak sesuai"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

// Verify password matches using password_verify()
if (!password_verify($inputPassword, $oldData['password'])) {
    $response = [
        "success" => false,
        "message" => "password tidak sesuai"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$stmt->close();

// Check for duplicate username if username is being changed
if (isset($inputData['new_username']) && !empty(trim($inputData['new_username']))) {
    $newUsername = trim($inputData['new_username']);

    $checkQuery = "SELECT id_users FROM users WHERE username = ? AND id_users != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $newUsername, $id_users);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = [
            "success" => false,
            "message" => "username baru sudah ada, tidak boleh duplikat"
        ];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Hash new password if provided
    $passwordHash = null;
    if (isset($inputData['new_password']) && !empty(trim($inputData['new_password']))) {
        $newPassword = trim($inputData['new_password']);

        // Validate password minimum length
        if (strlen($newPassword) < 8) {
            $response = [
                "success" => false,
                "message" => "password baru minimal 8 karakter"
            ];
            echo json_encode($response);
            exit;
        }

        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);
    }

    // Update with new username and optional new password
    $aktif = isset($inputData['aktif']) ? (int)$inputData['aktif'] : $oldData['aktif'];

    if ($passwordHash) {
        $query = "UPDATE users SET username = ?, password = ?, aktif = ? WHERE id_users = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssii", $newUsername, $passwordHash, $aktif, $id_users);
    } else {
        $query = "UPDATE users SET username = ?, aktif = ? WHERE id_users = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sii", $newUsername, $aktif, $id_users);
    }
} else {
    // Only update aktif status (no username/password change)
    $aktif = isset($inputData['aktif']) ? (int)$inputData['aktif'] : $oldData['aktif'];

    $query = "UPDATE users SET aktif = ? WHERE id_users = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $aktif, $id_users);
}

if ($stmt->execute()) {
    // Fetch updated data
    $fetchQuery = "SELECT id_users, username, aktif, created_at, last_login FROM users WHERE id_users = ?";
    $fetchStmt = $conn->prepare($fetchQuery);
    $fetchStmt->bind_param("i", $id_users);
    $fetchStmt->execute();
    $updatedData = $fetchStmt->get_result()->fetch_assoc();
    $fetchStmt->close();

    $responseData = [
        "id_users" => (int)$updatedData['id_users'],
        "username" => $updatedData['username'],
        "aktif" => (int)$updatedData['aktif'],
        "created_at" => $updatedData['created_at'],
        "last_login" => $updatedData['last_login']
    ];

    $response = [
        "success" => true,
        "message" => "Data user berhasil diupdate",
        "data" => $responseData
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal mengupdate data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
