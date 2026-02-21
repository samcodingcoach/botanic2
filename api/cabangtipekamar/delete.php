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

// Parse JSON body
$inputData = [];
$rawInput = file_get_contents('php://input');
$jsonData = json_decode($rawInput, true);

if ($jsonData !== null) {
    $inputData = $jsonData;
} else {
    $inputData = $_POST;
}

// Validate required field: id_akomodasi
if (!isset($inputData['id_akomodasi']) || empty($inputData['id_akomodasi'])) {
    $response = [
        "success" => false,
        "message" => "id_akomodasi wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_akomodasi = (int) $inputData['id_akomodasi'];

// Check if id_akomodasi exists
$checkQuery = "SELECT id_akomodasi FROM cabang_tipe WHERE id_akomodasi = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_akomodasi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data cabang tipe tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Delete record from database
$deleteQuery = "DELETE FROM cabang_tipe WHERE id_akomodasi = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id_akomodasi);

if ($stmt->execute()) {
    $response = [
        "success" => true,
        "message" => "Data cabang tipe berhasil dihapus",
        "data" => [
            "id_akomodasi" => $id_akomodasi
        ]
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
