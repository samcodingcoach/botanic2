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
// Always try to parse JSON body first
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

if ($jsonData !== null) {
    $inputData = $jsonData;
} else {
    $inputData = $_POST;
}

// Validate required field: id_fasilitas
if (!isset($inputData['id_fasilitas']) || empty($inputData['id_fasilitas'])) {
    $response = [
        "success" => false,
        "message" => "id_fasilitas wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_fasilitas = (int) $inputData['id_fasilitas'];

// Check if id_fasilitas exists and get old data
$checkQuery = "SELECT id_fasilitas, id_cabang, nama_fasilitas, gambar1, gambar2 FROM fasilitas WHERE id_fasilitas = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_fasilitas);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data fasilitas tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldIdCabang = $oldData['id_cabang'];
$oldNamaFasilitas = $oldData['nama_fasilitas'];
$oldGambar1 = $oldData['gambar1'];
$oldGambar2 = $oldData['gambar2'];
$stmt->close();

// Validate id_cabang if provided
if (isset($inputData['id_cabang'])) {
    $id_cabang = (int) $inputData['id_cabang'];
} else {
    $id_cabang = $oldIdCabang;
}

// Validate nama_fasilitas if provided
if (isset($inputData['nama_fasilitas'])) {
    $nama_fasilitas = trim($inputData['nama_fasilitas']);
    if (empty($nama_fasilitas)) {
        $response = [
            "success" => false,
            "message" => "nama_fasilitas tidak boleh kosong"
        ];
        echo json_encode($response);
        exit;
    }
} else {
    $nama_fasilitas = $oldNamaFasilitas;
}

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
$gambar1Name = $oldGambar1;
$deleteOldGambar1 = false;
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
    $deleteOldGambar1 = true;
}

// Upload gambar2
$gambar2Name = $oldGambar2;
$deleteOldGambar2 = false;
if (isset($_FILES['gambar2']) && $_FILES['gambar2']['error'] !== UPLOAD_ERR_NO_FILE) {
    $result2 = uploadImage($_FILES['gambar2'], $uploadDir, $nama_fasilitas, $maxFileSize, $allowedExtensions);
    if (is_array($result2) && isset($result2['error'])) {
        // Delete gambar1 if already uploaded and new
        if ($deleteOldGambar1 && $gambar1Name !== $oldGambar1 && file_exists($uploadDir . $gambar1Name)) {
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
    $deleteOldGambar2 = true;
}

// Prepare update query
$query = "UPDATE fasilitas
          SET id_cabang = ?, nama_fasilitas = ?, deskripsi = ?, gambar1 = ?, gambar2 = ?, aktif = ?, status_free = ?, range_harga = ?
          WHERE id_fasilitas = ?";

$stmt = $conn->prepare($query);

$deskripsi = isset($inputData['deskripsi']) ? trim($inputData['deskripsi']) : null;
$aktif = isset($inputData['aktif']) ? (int) $inputData['aktif'] : 1;
$status_free = isset($inputData['status_free']) ? (int) $inputData['status_free'] : 0;
$range_harga = isset($inputData['range_harga']) ? (float) $inputData['range_harga'] : null;

$stmt->bind_param("issssiidi", $id_cabang, $nama_fasilitas, $deskripsi, $gambar1Name, $gambar2Name, $aktif, $status_free, $range_harga, $id_fasilitas);

if ($stmt->execute()) {
    // Delete old images if new ones uploaded
    if ($deleteOldGambar1 && !empty($oldGambar1) && $oldGambar1 !== $gambar1Name && file_exists($uploadDir . $oldGambar1)) {
        unlink($uploadDir . $oldGambar1);
    }
    if ($deleteOldGambar2 && !empty($oldGambar2) && $oldGambar2 !== $gambar2Name && file_exists($uploadDir . $oldGambar2)) {
        unlink($uploadDir . $oldGambar2);
    }

    $responseData = [
        "id_fasilitas" => $id_fasilitas,
        "id_cabang" => $id_cabang,
        "nama_fasilitas" => $nama_fasilitas,
        "gambar1" => $gambar1Name,
        "gambar2" => $gambar2Name
    ];

    $response = [
        "success" => true,
        "message" => "Data fasilitas berhasil diupdate",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded files if update fails
    if ($deleteOldGambar1 && $gambar1Name !== $oldGambar1 && file_exists($uploadDir . $gambar1Name)) {
        unlink($uploadDir . $gambar1Name);
    }
    if ($deleteOldGambar2 && $gambar2Name !== $oldGambar2 && file_exists($uploadDir . $gambar2Name)) {
        unlink($uploadDir . $gambar2Name);
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
