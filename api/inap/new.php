<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1); // Display errors for debugging
ini_set('log_errors', 1);

// Start output buffering to capture any stray output
ob_start();

header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Register shutdown function to catch any fatal errors
register_shutdown_function(function() {
    $error = error_get_last();
    if ($error && ($error['type'] === E_ERROR || $error['type'] === E_PARSE)) {
        ob_clean();
        echo json_encode([
            "success" => false,
            "message" => "PHP Fatal Error: " . $error['message'],
            "file" => $error['file'],
            "line" => $error['line']
        ]);
    }
    ob_end_flush();
});

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// Set error handler to catch errors and return as JSON
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    ob_clean();
    header("Content-Type: application/json");
    echo json_encode([
        "success" => false,
        "message" => "PHP Error: " . $errstr,
        "error_file" => $errfile,
        "error_line" => $errline
    ]);
    exit;
});

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

try {
    // Validate required fields
    $requiredFields = ['id_cabang', 'id_akomodasi', 'id_guest', 'kode_booking', 'nomor_kamar', 'tanggal_in', 'tanggal_out', 'id_users'];
    foreach ($requiredFields as $field) {
        if (!isset($inputData[$field]) || (is_string($inputData[$field]) && trim($inputData[$field]) === '')) {
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
    $status = isset($inputData['status']) && $inputData['status'] !== '' ? (int) $inputData['status'] : 0;
    $ota = isset($inputData['ota']) ? trim($inputData['ota']) : null;
    $id_users = isset($inputData['id_users']) ? (int) $inputData['id_users'] : 0;

    if ($id_users === 0) {
        $response = [
            "success" => false,
            "message" => "id_users wajib diisi"
        ];
        echo json_encode($response);
        exit;
    }

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
    $stmt->bind_param("iiissssissi", $id_cabang, $id_akomodasi, $id_guest, $kode_booking, $nomor_kamar, $tanggal_in, $tanggal_out, $status, $ota, $link_receipt, $id_users);

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

    ob_end_clean();
    header("Content-Type: application/json");
    echo json_encode($response);
    
} catch (Exception $e) {
    ob_end_clean();
    header("Content-Type: application/json");
    $response = [
        "success" => false,
        "message" => "Exception: " . $e->getMessage(),
        "file" => $e->getFile(),
        "line" => $e->getLine()
    ];
    echo json_encode($response);
}
?>
