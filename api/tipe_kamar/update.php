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
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);
} else {
    $inputData = $_POST;
}

// Validate required field: id_tipe
if (!isset($inputData['id_tipe']) || empty($inputData['id_tipe'])) {
    $response = [
        "success" => false,
        "message" => "id_tipe wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_tipe = (int) $inputData['id_tipe'];

// Check if id_tipe exists
$checkQuery = "SELECT id_tipe, nama_tipe, gambar FROM tipe_kamar WHERE id_tipe = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_tipe);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data tipe kamar tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldNamaTipe = $oldData['nama_tipe'];
$oldGambar = $oldData['gambar'];
$stmt->close();

// Validate nama_tipe if provided
if (isset($inputData['nama_tipe'])) {
    $nama_tipe = trim($inputData['nama_tipe']);

    if (empty($nama_tipe)) {
        $response = [
            "success" => false,
            "message" => "nama_tipe tidak boleh kosong"
        ];
        echo json_encode($response);
        exit;
    }

    // Check for duplicate nama_tipe (exclude current record)
    $checkQuery = "SELECT id_tipe FROM tipe_kamar WHERE nama_tipe = ? AND id_tipe != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $nama_tipe, $id_tipe);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = [
            "success" => false,
            "message" => "nama_tipe sudah ada, tidak boleh duplikat"
        ];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();
} else {
    $nama_tipe = $oldNamaTipe;
}

// Handle image upload
$gambarName = null;
$deleteOldGambar = false;
$uploadDir = __DIR__ . '/../../images/';

// Check for file in form-data
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    // Validate file size
    if ($_FILES['gambar']['size'] > 1 * 1024 * 1024) {
        $response = [
            "success" => false,
            "message" => "Ukuran file terlalu besar. Maksimal 1MB."
        ];
        echo json_encode($response);
        exit;
    }

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $gambarName = 'tipe_' . preg_replace('/[^a-zA-Z0-9]/', '_', $nama_tipe) . '_' . $timestamp . '.' . $fileExtension;
    $uploadPath = $uploadDir . $gambarName;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        $response = [
            "success" => false,
            "message" => "Ekstensi file tidak diperbolehkan. Gunakan: jpg, jpeg, png, gif, webp"
        ];
        echo json_encode($response);
        exit;
    }

    if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $uploadPath)) {
        $response = [
            "success" => false,
            "message" => "Gagal mengupload gambar"
        ];
        echo json_encode($response);
        exit;
    }

    // Mark old gambar for deletion
    $deleteOldGambar = true;
} else {
    // Keep old gambar if no new upload
    $gambarName = $oldGambar;
}

// Prepare update query
$query = "UPDATE tipe_kamar
          SET nama_tipe = ?, gambar = ?, keterangan = ?
          WHERE id_tipe = ?";

$stmt = $conn->prepare($query);

$keterangan = isset($inputData['keterangan']) ? trim($inputData['keterangan']) : null;

$stmt->bind_param("sssi", $nama_tipe, $gambarName, $keterangan, $id_tipe);

if ($stmt->execute()) {
    // Delete old gambar if new one uploaded
    if ($deleteOldGambar && !empty($oldGambar) && file_exists($uploadDir . $oldGambar)) {
        unlink($uploadDir . $oldGambar);
    }

    $responseData = [
        "id_tipe" => $id_tipe,
        "nama_tipe" => $nama_tipe,
        "gambar" => $gambarName
    ];

    $response = [
        "success" => true,
        "message" => "Data tipe kamar berhasil diupdate",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if update fails
    if ($deleteOldGambar && $gambarName && file_exists($uploadDir . $gambarName)) {
        unlink($uploadDir . $gambarName);
    }

    $response = [
        "success" => false,
        "message" => "Gagal mengupdate data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
