<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// Support both POST and DELETE methods
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    $response = [
        "success" => false,
        "message" => "Method not allowed. Use POST or DELETE method."
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

// Validate required field: id_fo
if (!isset($inputData['id_fo']) || empty($inputData['id_fo'])) {
    $response = [
        "success" => false,
        "message" => "id_fo wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_fo = (int) $inputData['id_fo'];

// Check if id_fo exists
$checkQuery = "SELECT id_fo FROM front_office WHERE id_fo = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_fo);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data front office tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Prepare delete query
$query = "DELETE FROM front_office WHERE id_fo = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_fo);

if ($stmt->execute()) {
    $response = [
        "success" => true,
        "message" => "Data front office berhasil dihapus",
        "data" => [
            "id_fo" => $id_fo
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
