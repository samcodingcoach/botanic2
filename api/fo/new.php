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

// Validate required field: wa
if (!isset($inputData['wa']) || empty(trim($inputData['wa']))) {
    $response = [
        "success" => false,
        "message" => "wa wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$wa = trim($inputData['wa']);

// Check for duplicate wa
$checkQuery = "SELECT id_fo FROM front_office WHERE wa = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("s", $wa);
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

// Validate required field: id_cabang
if (!isset($inputData['id_cabang']) || empty(trim($inputData['id_cabang']))) {
    $response = [
        "success" => false,
        "message" => "id_cabang wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_cabang = trim($inputData['id_cabang']);
$aktif = isset($inputData['aktif']) ? (int)$inputData['aktif'] : 1;

// Prepare insert query (exclude id_fo)
$query = "INSERT INTO front_office (wa, id_cabang, aktif)
          VALUES (?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param("sii", $wa, $id_cabang, $aktif);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_fo" => $newId,
        "wa" => $wa,
        "id_cabang" => $id_cabang,
        "aktif" => $aktif
    ];

    $response = [
        "success" => true,
        "message" => "Data front office berhasil ditambahkan",
        "data" => $responseData
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal menyimpan data: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
