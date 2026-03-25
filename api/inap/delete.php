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

// Support JSON body
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

// Check if id_inap exists and get receipt file
$checkQuery = "SELECT link_receipt FROM inap WHERE id_inap = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_inap);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data reservasi tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$row = $result->fetch_assoc();
$link_receipt = $row['link_receipt'];
$stmt->close();

// Delete the record
$deleteQuery = "DELETE FROM inap WHERE id_inap = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id_inap);

if ($stmt->execute()) {
    // Delete receipt file if exists
    if (!empty($link_receipt)) {
        $receiptPath = __DIR__ . '/../../receipt/' . $link_receipt;
        if (file_exists($receiptPath)) {
            unlink($receiptPath);
        }
    }

    $response = [
        "success" => true,
        "message" => "Data reservasi berhasil dihapus"
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal menghapus data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
