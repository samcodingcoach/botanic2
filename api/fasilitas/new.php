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
// Always try to parse JSON body first
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

if ($jsonData !== null) {
    $inputData = $jsonData;
} else {
    $inputData = $_POST;
}

// Validate required field: id_cabang
if (!isset($inputData['id_cabang']) || empty($inputData['id_cabang'])) {
    $response = [
        "success" => false,
        "message" => "id_cabang wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

// Validate required field: nama_fasilitas
if (!isset($inputData['nama_fasilitas']) || empty(trim($inputData['nama_fasilitas']))) {
    $response = [
        "success" => false,
        "message" => "nama_fasilitas wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_cabang = (int) $inputData['id_cabang'];
$nama_fasilitas = trim($inputData['nama_fasilitas']);

// Handle image uploads
$uploadDir = __DIR__ . '/../../images/';
$maxFileSize = 1 * 1024 * 1024; // 1MB in bytes
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

// Function to handle image upload
function uploadImage($file, $uploadDir, $namaFasilitas, $maxSize, $allowedExt) {
    if (!isset($file) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['error' => 'Gagal mengupload file'];
    }

    // Validate file size
    if ($file['size'] > $maxSize) {
        return ['error' => 'Ukuran file terlalu besar. Maksimal 1MB.'];
    }

    // Validate file type
    $validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $validTypes)) {
        return ['error' => 'Tipe file tidak valid. Gunakan JPG, PNG, GIF, atau WEBP.'];
    }

    // Create directory if not exists
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $imageName = 'fasilitas_' . preg_replace('/[^a-zA-Z0-9]/', '_', $namaFasilitas) . '_' . $timestamp . '.' . $fileExtension;
    $uploadPath = $uploadDir . $imageName;

    if (!move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return ['error' => 'Gagal mengupload gambar'];
    }

    return $imageName;
}

// Upload gambar1
$gambar1Name = null;
if (isset($_FILES['gambar1']) && $_FILES['gambar1']['error'] !== UPLOAD_ERR_NO_FILE) {
    $result1 = uploadImage($_FILES['gambar1'], $uploadDir, $nama_fasilitas, $maxFileSize, $allowedExtensions);
    if (is_array($result1) && isset($result1['error'])) {
        $response = [
            "success" => false,
            "message" => $result1['error']
        ];
        echo json_encode($response);
        exit;
    }
    $gambar1Name = $result1;
}

// Upload gambar2
$gambar2Name = null;
if (isset($_FILES['gambar2']) && $_FILES['gambar2']['error'] !== UPLOAD_ERR_NO_FILE) {
    $result2 = uploadImage($_FILES['gambar2'], $uploadDir, $nama_fasilitas, $maxFileSize, $allowedExtensions);
    if (is_array($result2) && isset($result2['error'])) {
        // Delete gambar1 if already uploaded
        if ($gambar1Name && file_exists($uploadDir . $gambar1Name)) {
            unlink($uploadDir . $gambar1Name);
        }
        $response = [
            "success" => false,
            "message" => $result2['error']
        ];
        echo json_encode($response);
        exit;
    }
    $gambar2Name = $result2;
}

// Prepare insert query (exclude id_fasilitas and created_at - auto generated)
$query = "INSERT INTO fasilitas (id_cabang, nama_fasilitas, deskripsi, gambar1, gambar2, aktif, status_free, range_harga)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);

$deskripsi = isset($inputData['deskripsi']) ? trim($inputData['deskripsi']) : null;
$aktif = isset($inputData['aktif']) ? (int) $inputData['aktif'] : 1;
$status_free = isset($inputData['status_free']) ? (int) $inputData['status_free'] : 0;
$range_harga = isset($inputData['range_harga']) ? (float) $inputData['range_harga'] : null;

$stmt->bind_param("issssidi", $id_cabang, $nama_fasilitas, $deskripsi, $gambar1Name, $gambar2Name, $aktif, $status_free, $range_harga);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_fasilitas" => $newId,
        "id_cabang" => $id_cabang,
        "nama_fasilitas" => $nama_fasilitas,
        "gambar1" => $gambar1Name,
        "gambar2" => $gambar2Name
    ];

    $response = [
        "success" => true,
        "message" => "Data fasilitas berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded files if insert fails
    if ($gambar1Name && file_exists($uploadDir . $gambar1Name)) {
        unlink($uploadDir . $gambar1Name);
    }
    if ($gambar2Name && file_exists($uploadDir . $gambar2Name)) {
        unlink($uploadDir . $gambar2Name);
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
