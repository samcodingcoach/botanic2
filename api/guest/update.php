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
$checkQuery = "SELECT id_guest, password, email, wa, aktif FROM guest WHERE id_guest = ?";
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

$oldData = $result->fetch_assoc();

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

// Check for duplicate email (only if email is provided and being changed)
if (isset($inputData['email']) && !empty(trim($inputData['email']))) {
    $newEmail = trim($inputData['email']);
    $oldEmail = trim($oldData['email'] ?? '');
    
    if ($newEmail !== $oldEmail) {
        $checkEmailQuery = "SELECT id_guest FROM guest WHERE email = ? AND id_guest != ?";
        $stmt = $conn->prepare($checkEmailQuery);
        $stmt->bind_param("si", $newEmail, $id_guest);
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
}

// Check for duplicate wa (if wa is being changed)
if (isset($inputData['wa']) && !empty(trim($inputData['wa']))) {
    $newWa = trim($inputData['wa']);
    $oldWa = trim($oldData['wa'] ?? '');

    if ($newWa !== $oldWa) {
        $checkWaQuery = "SELECT id_guest FROM guest WHERE wa = ? AND id_guest != ?";
        $stmt = $conn->prepare($checkWaQuery);
        $stmt->bind_param("si", $newWa, $id_guest);
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
    }
}

// Build update query dynamically based on provided fields
$updateFields = [];
$types = "";
$params = [];

// nama_lengkap
if (isset($inputData['nama_lengkap']) && !empty(trim($inputData['nama_lengkap']))) {
    $updateFields[] = "nama_lengkap = ?";
    $types .= "s";
    $params[] = trim($inputData['nama_lengkap']);
}

// email (optional, can be NULL)
if (isset($inputData['email'])) {
    $updateFields[] = "email = ?";
    $types .= "s";
    $emailValue = !empty(trim($inputData['email'])) ? trim($inputData['email']) : null;
    $params[] = $emailValue;
}

// wa
if (isset($inputData['wa']) && !empty(trim($inputData['wa']))) {
    $updateFields[] = "wa = ?";
    $types .= "s";
    $params[] = trim($inputData['wa']);
}

// password (optional)
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
    
    $updateFields[] = "password = ?";
    $types .= "s";
    $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
}

// kota
if (isset($inputData['kota'])) {
    $updateFields[] = "kota = ?";
    $types .= "s";
    $params[] = trim($inputData['kota']);
}

// aktif
if (isset($inputData['aktif'])) {
    $updateFields[] = "aktif = ?";
    $types .= "i";
    $params[] = (int)$inputData['aktif'];
}

// If no fields to update
if (empty($updateFields)) {
    $response = [
        "success" => false,
        "message" => "Tidak ada data yang diupdate"
    ];
    echo json_encode($response);
    exit;
}

// Build final query
$query = "UPDATE guest SET " . implode(", ", $updateFields) . " WHERE id_guest = ?";
$types .= "i";
$params[] = $id_guest;

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);

if ($stmt->execute()) {
    // Fetch updated data
    $fetchQuery = "SELECT 
        id_guest,
        nama_lengkap,
        email,
        wa,
        kota,
        aktif,
        total_point,
        created_at,
        last_login
    FROM guest WHERE id_guest = ?";
    $fetchStmt = $conn->prepare($fetchQuery);
    $fetchStmt->bind_param("i", $id_guest);
    $fetchStmt->execute();
    $updatedData = $fetchStmt->get_result()->fetch_assoc();
    $fetchStmt->close();

    $responseData = [
        "id_guest" => (int)$updatedData['id_guest'],
        "nama_lengkap" => $updatedData['nama_lengkap'],
        "email" => $updatedData['email'],
        "wa" => $updatedData['wa'],
        "kota" => $updatedData['kota'],
        "aktif" => (int)$updatedData['aktif'],
        "total_point" => (float)$updatedData['total_point'],
        "created_at" => $updatedData['created_at'],
        "last_login" => $updatedData['last_login']
    ];

    $response = [
        "success" => true,
        "message" => "Data guest berhasil diupdate",
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
