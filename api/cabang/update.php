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

// Validate required field: id_cabang
if (!isset($inputData['id_cabang']) || empty($inputData['id_cabang'])) {
    $response = [
        "success" => false,
        "message" => "id_cabang wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_cabang = (int) $inputData['id_cabang'];

// Check if id_cabang exists
$checkQuery = "SELECT id_cabang, kode_cabang, foto FROM Cabang WHERE id_cabang = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_cabang);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data cabang tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldKodeCabang = $oldData['kode_cabang'];
$oldFoto = $oldData['foto'];
$stmt->close();

// Validate kode_cabang if provided
if (isset($inputData['kode_cabang'])) {
    $kode_cabang = trim($inputData['kode_cabang']);
    
    if (empty($kode_cabang)) {
        $response = [
            "success" => false,
            "message" => "kode_cabang tidak boleh kosong"
        ];
        echo json_encode($response);
        exit;
    }
    
    // Check for duplicate kode_cabang (exclude current record)
    $checkQuery = "SELECT id_cabang FROM Cabang WHERE kode_cabang = ? AND id_cabang != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $kode_cabang, $id_cabang);
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
} else {
    $kode_cabang = $oldKodeCabang;
}

// Handle image upload
$fotoName = null;
$deleteOldFoto = false;
$uploadDir = __DIR__ . '/../../images/';

// Check for file in form-data
if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
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
    
    // Mark old foto for deletion
    $deleteOldFoto = true;
} else {
    // Keep old foto if no new upload
    $fotoName = $oldFoto;
}

// Prepare update query
$query = "UPDATE Cabang 
          SET nama_cabang = ?, alamat = ?, gps = ?, kode_cabang = ?, foto = ?, hp = ? 
          WHERE id_cabang = ?";

$stmt = $conn->prepare($query);

$nama_cabang = isset($inputData['nama_cabang']) ? trim($inputData['nama_cabang']) : null;
$alamat = isset($inputData['alamat']) ? trim($inputData['alamat']) : null;
$gps = isset($inputData['gps']) ? trim($inputData['gps']) : null;
$hp = isset($inputData['hp']) ? trim($inputData['hp']) : null;

$stmt->bind_param("ssssssi", $nama_cabang, $alamat, $gps, $kode_cabang, $fotoName, $hp, $id_cabang);

if ($stmt->execute()) {
    // Delete old foto if new one uploaded
    if ($deleteOldFoto && !empty($oldFoto) && file_exists($uploadDir . $oldFoto)) {
        unlink($uploadDir . $oldFoto);
    }

    $responseData = [
        "id_cabang" => $id_cabang,
        "kode_cabang" => $kode_cabang,
        "nama_cabang" => $nama_cabang,
        "foto" => $fotoName
    ];

    $response = [
        "success" => true,
        "message" => "Data cabang berhasil diupdate",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if update fails
    if ($deleteOldFoto && $fotoName && file_exists($uploadDir . $fotoName)) {
        unlink($uploadDir . $fotoName);
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
