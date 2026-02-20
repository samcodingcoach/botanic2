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
if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false) {
    $rawInput = file_get_contents('php://input');
    $inputData = json_decode($rawInput, true);
} else {
    $inputData = $_POST;
}

// Validate required field: nama_tipe
if (!isset($inputData['nama_tipe']) || empty(trim($inputData['nama_tipe']))) {
    $response = [
        "success" => false,
        "message" => "nama_tipe wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$nama_tipe = trim($inputData['nama_tipe']);

// Check for duplicate nama_tipe
$checkQuery = "SELECT id_tipe FROM tipe_kamar WHERE nama_tipe = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $nama_tipe);
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

// Handle image upload
$gambarName = null;
$uploadDir = __DIR__ . '/../../images/';
$maxFileSize = 1 * 1024 * 1024; // 1MB in bytes

// Check for file in form-data
if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
    // Validate file size
    if ($_FILES['gambar']['size'] > $maxFileSize) {
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
}

// Prepare insert query (exclude id_tipe)
$query = "INSERT INTO tipe_kamar (nama_tipe, gambar, keterangan)
          VALUES (?, ?, ?)";

$stmt = $conn->prepare($query);

$keterangan = isset($inputData['keterangan']) ? trim($inputData['keterangan']) : null;

$stmt->bind_param("sss", $nama_tipe, $gambarName, $keterangan);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_tipe" => $newId,
        "nama_tipe" => $nama_tipe,
        "gambar" => $gambarName
    ];

    $response = [
        "success" => true,
        "message" => "Data tipe kamar berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if insert fails
    if ($gambarName && file_exists($uploadDir . $gambarName)) {
        unlink($uploadDir . $gambarName);
    }

    $response = [
        "success" => false,
        "message" => "Gagal menyimpan data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
