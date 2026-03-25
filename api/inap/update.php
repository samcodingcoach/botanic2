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

// Validate required field: id_inap
if (!isset($inputData['id_inap']) || empty($inputData['id_inap'])) {
    $response = [
        "success" => false,
        "message" => "id_inap wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_inap = (int) $inputData['id_inap'];

// Check if id_inap exists
$checkQuery = "SELECT id_inap, kode_booking, link_receipt FROM inap WHERE id_inap = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_inap);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data inap tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldKodeBooking = $oldData['kode_booking'];
$oldLinkReceipt = $oldData['link_receipt'];
$stmt->close();

// Validate and set fields
$fields = ['id_cabang', 'id_akomodasi', 'id_guest', 'kode_booking', 'nomor_kamar', 'tanggal_in', 'tanggal_out', 'status', 'ota', 'id_users'];
$data = [];

foreach ($fields as $field) {
    if (isset($inputData[$field])) {
        if (in_array($field, ['id_cabang', 'id_akomodasi', 'id_guest', 'status', 'id_users'])) {
            $data[$field] = (int) $inputData[$field];
        } else {
            $data[$field] = trim($inputData[$field]);
        }
    }
}

// Check for duplicate kode_booking (exclude current record)
if (isset($inputData['kode_booking'])) {
    $checkQuery = "SELECT id_inap FROM inap WHERE kode_booking = ? AND id_inap != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $data['kode_booking'], $id_inap);
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
} else {
    $data['kode_booking'] = $oldKodeBooking;
}

// Handle receipt upload
$link_receipt = null;
$deleteOldReceipt = false;
$uploadDir = __DIR__ . '/../../receipt/';

if (isset($_FILES['link_receipt']) && $_FILES['link_receipt']['error'] === UPLOAD_ERR_OK) {
    if ($_FILES['link_receipt']['size'] > 5 * 1024 * 1024) {
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
    $link_receipt = 'receipt_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['kode_booking']) . '_' . $timestamp . '.' . $fileExtension;
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

    $deleteOldReceipt = true;
} else {
    $link_receipt = $oldLinkReceipt;
}

// Prepare update query
$query = "UPDATE inap
          SET id_cabang = ?, id_akomodasi = ?, id_guest = ?, kode_booking = ?, nomor_kamar = ?, tanggal_in = ?, tanggal_out = ?, status = ?, ota = ?, link_receipt = ?, id_users = ?
          WHERE id_inap = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("iiissssisssi", 
    $data['id_cabang'], 
    $data['id_akomodasi'], 
    $data['id_guest'], 
    $data['kode_booking'], 
    $data['nomor_kamar'], 
    $data['tanggal_in'], 
    $data['tanggal_out'], 
    $data['status'], 
    $data['ota'], 
    $link_receipt, 
    $data['id_users'], 
    $id_inap
);

if ($stmt->execute()) {
    // Delete old receipt if new one uploaded
    if ($deleteOldReceipt && !empty($oldLinkReceipt) && file_exists($uploadDir . $oldLinkReceipt)) {
        unlink($uploadDir . $oldLinkReceipt);
    }

    $responseData = [
        "id_inap" => $id_inap,
        "id_cabang" => $data['id_cabang'],
        "id_akomodasi" => $data['id_akomodasi'],
        "id_guest" => $data['id_guest'],
        "kode_booking" => $data['kode_booking'],
        "nomor_kamar" => $data['nomor_kamar'],
        "tanggal_in" => $data['tanggal_in'],
        "tanggal_out" => $data['tanggal_out'],
        "status" => $data['status'],
        "ota" => $data['ota'],
        "link_receipt" => $link_receipt,
        "id_users" => $data['id_users']
    ];

    $response = [
        "success" => true,
        "message" => "Data inap berhasil diupdate",
        "data" => $responseData
    ];
} else {
    // Rollback: delete uploaded file if update fails
    if ($deleteOldReceipt && $link_receipt && file_exists($uploadDir . $link_receipt)) {
        unlink($uploadDir . $link_receipt);
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
