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
$checkQuery = "SELECT id_fo, wa FROM front_office WHERE id_fo = ?";
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

$oldData = $result->fetch_assoc();
$oldWa = $oldData['wa'];
$stmt->close();

// Validate wa if provided
if (isset($inputData['wa'])) {
    $wa = trim($inputData['wa']);

    if (empty($wa)) {
        $response = [
            "success" => false,
            "message" => "wa tidak boleh kosong"
        ];
        echo json_encode($response);
        exit;
    }

    // Check for duplicate wa (exclude current record)
    $checkQuery = "SELECT id_fo FROM front_office WHERE wa = ? AND id_fo != ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("si", $wa, $id_fo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = [
            "success" => false,
            "message" => "wa sudah ada, tidak boleh duplikat"
        ];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();
} else {
    $wa = $oldWa;
}

// Validate id_cabang if provided
if (isset($inputData['id_cabang'])) {
    $id_cabang = (int) $inputData['id_cabang'];
} else {
    $id_cabang = null;
}

// Handle aktif field
if (isset($inputData['aktif'])) {
    $aktif = (int) $inputData['aktif'];
} else {
    $aktif = null;
}

// Prepare update query
$query = "UPDATE front_office
          SET wa = ?, id_cabang = ?, aktif = ?
          WHERE id_fo = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("siii", $wa, $id_cabang, $aktif, $id_fo);

if ($stmt->execute()) {
    $responseData = [
        "id_fo" => $id_fo,
        "wa" => $wa,
        "id_cabang" => $id_cabang,
        "aktif" => $aktif
    ];

    $response = [
        "success" => true,
        "message" => "Data front office berhasil diupdate",
        "data" => $responseData
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal mengupdate data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
