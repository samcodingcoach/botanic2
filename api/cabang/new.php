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

// Validate required field: kode_cabang
if (!isset($inputData['kode_cabang']) || empty(trim($inputData['kode_cabang']))) {
    $response = [
        "success" => false,
        "message" => "kode_cabang wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$kode_cabang = trim($inputData['kode_cabang']);

// Check for duplicate kode_cabang
$checkQuery = "SELECT id_cabang FROM Cabang WHERE kode_cabang = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $kode_cabang);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response = [
        "success" => false,
        "message" => "kode_cabang sudah ada, tidak boleh duplikat"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Handle image upload
$fotoName = null;
$uploadDir = __DIR__ . '/../../images/';
$maxFileSize = 1 * 1024 * 1024; // 1MB in bytes

// Check for file in form-data
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
    // Validate file size
    if ($_FILES['foto']['size'] > $maxFileSize) {
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

    $fileExtension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $fotoName = $kode_cabang . '_' . $timestamp . '.' . $fileExtension;
    $uploadPath = $uploadDir . $fotoName;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        $response = [
            "success" => false,
            "message" => "Ekstensi file tidak diperbolehkan. Gunakan: jpg, jpeg, png, gif, webp"
        ];
        echo json_encode($response);
        exit;
    }

    if (!move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
        $response = [
            "success" => false,
            "message" => "Gagal mengupload foto"
        ];
        echo json_encode($response);
        exit;
    }
}

// Prepare insert query (exclude id_cabang and created_date)
$query = "INSERT INTO Cabang (nama_cabang, alamat, gps, kode_cabang, foto, hp) 
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

$nama_cabang = isset($inputData['nama_cabang']) ? trim($inputData['nama_cabang']) : null;
$alamat = isset($inputData['alamat']) ? trim($inputData['alamat']) : null;
$gps = isset($inputData['gps']) ? trim($inputData['gps']) : null;
$hp = isset($inputData['hp']) ? trim($inputData['hp']) : null;

$stmt->bind_param("ssssss", $nama_cabang, $alamat, $gps, $kode_cabang, $fotoName, $hp);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_cabang" => $newId,
        "kode_cabang" => $kode_cabang,
        "nama_cabang" => $nama_cabang,
        "foto" => $fotoName
    ];

    $response = [
        "success" => true,
        "message" => "Data cabang berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if insert fails
    if ($fotoName && file_exists($uploadDir . $fotoName)) {
        unlink($uploadDir . $fotoName);
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
