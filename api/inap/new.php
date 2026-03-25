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

// Validate required fields
$requiredFields = ['id_cabang', 'id_akomodasi', 'id_guest', 'kode_booking', 'nomor_kamar', 'tanggal_in', 'tanggal_out', 'id_users'];
foreach ($requiredFields as $field) {
    if (!isset($inputData[$field]) || empty($inputData[$field])) {
        $response = [
            "success" => false,
            "message" => "$field wajib diisi"
        ];
        echo json_encode($response);
        exit;
    }
}

$id_cabang = (int) $inputData['id_cabang'];
$id_akomodasi = (int) $inputData['id_akomodasi'];
$id_guest = (int) $inputData['id_guest'];
$kode_booking = trim($inputData['kode_booking']);
$nomor_kamar = trim($inputData['nomor_kamar']);
$tanggal_in = trim($inputData['tanggal_in']);
$tanggal_out = trim($inputData['tanggal_out']);
$status = isset($inputData['status']) ? (int) $inputData['status'] : 0;
$ota = isset($inputData['ota']) ? trim($inputData['ota']) : null;
$id_users = (int) $inputData['id_users'];

// Check for duplicate kode_booking
$checkQuery = "SELECT id_inap FROM inap WHERE kode_booking = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $kode_booking);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response = [
        "success" => false,
        "message" => "kode_booking sudah ada, tidak boleh duplikat"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Handle receipt upload (PDF/Image)
$link_receipt = null;
$uploadDir = __DIR__ . '/../../receipt/';
$maxFileSize = 5 * 1024 * 1024; // 5MB in bytes

if (isset($_FILES['link_receipt']) && $_FILES['link_receipt']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['link_receipt']['size'] > $maxFileSize) {
        $response = [
            "success" => false,
            "message" => "Ukuran file terlalu besar. Maksimal 5MB."
        ];
        echo json_encode($response);
        exit;
    }

    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES['link_receipt']['name'], PATHINFO_EXTENSION);
    $timestamp = time();
    $link_receipt = 'receipt_' . preg_replace('/[^a-zA-Z0-9]/', '_', $kode_booking) . '_' . $timestamp . '.' . $fileExtension;
    $uploadPath = $uploadDir . $link_receipt;

    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
    if (!in_array(strtolower($fileExtension), $allowedExtensions)) {
        $response = [
            "success" => false,
            "message" => "Ekstensi file tidak diperbolehkan. Gunakan: jpg, jpeg, png, gif, webp, pdf"
        ];
        echo json_encode($response);
        exit;
    }

    if (!move_uploaded_file($_FILES['link_receipt']['tmp_name'], $uploadPath)) {
        $response = [
            "success" => false,
            "message" => "Gagal mengupload receipt"
        ];
        echo json_encode($response);
        exit;
    }
}

// Prepare insert query
$query = "INSERT INTO inap (id_cabang, id_akomodasi, id_guest, kode_booking, nomor_kamar, tanggal_in, tanggal_out, status, ota, link_receipt, id_users)
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiissssiss", $id_cabang, $id_akomodasi, $id_guest, $kode_booking, $nomor_kamar, $tanggal_in, $tanggal_out, $status, $ota, $link_receipt, $id_users);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_inap" => $newId,
        "id_cabang" => $id_cabang,
        "id_akomodasi" => $id_akomodasi,
        "id_guest" => $id_guest,
        "kode_booking" => $kode_booking,
        "nomor_kamar" => $nomor_kamar,
        "tanggal_in" => $tanggal_in,
        "tanggal_out" => $tanggal_out,
        "status" => $status,
        "ota" => $ota,
        "link_receipt" => $link_receipt,
        "id_users" => $id_users
    ];

    $response = [
        "success" => true,
        "message" => "Data inap berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if insert fails
    if ($link_receipt && file_exists($uploadDir . $link_receipt)) {
        unlink($uploadDir . $link_receipt);
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
