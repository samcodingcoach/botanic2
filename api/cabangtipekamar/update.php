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
$checkQuery = "SELECT id_akomodasi, id_cabang, id_tipe FROM cabang_tipe WHERE id_akomodasi = ?";
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

$oldData = $result->fetch_assoc();
$oldIdCabang = $oldData['id_cabang'];
$oldIdTipe = $oldData['id_tipe'];
$stmt->close();

// Validate id_cabang if provided
if (isset($inputData['id_cabang'])) {
    $id_cabang = (int) $inputData['id_cabang'];
} else {
    $id_cabang = $oldIdCabang;
}

// Validate id_tipe if provided
if (isset($inputData['id_tipe'])) {
    $id_tipe = (int) $inputData['id_tipe'];
} else {
    $id_tipe = $oldIdTipe;
}

// Check for duplicate combination (exclude current record)
$checkQuery = "SELECT id_akomodasi FROM cabang_tipe WHERE id_cabang = ? AND id_tipe = ? AND id_akomodasi != ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("iii", $id_cabang, $id_tipe, $id_akomodasi);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $response = [
        "success" => false,
        "message" => "Kombinasi cabang dan tipe kamar sudah ada, tidak boleh duplikat"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}
$stmt->close();

// Prepare update query
$query = "UPDATE cabang_tipe
          SET id_cabang = ?, id_tipe = ?, keterangan = ?, link_youtube = ?
          WHERE id_akomodasi = ?";

$stmt = $conn->prepare($query);

$keterangan = isset($inputData['keterangan']) ? trim($inputData['keterangan']) : null;
$link_youtube = isset($inputData['link_youtube']) ? trim($inputData['link_youtube']) : null;

$stmt->bind_param("iissi", $id_cabang, $id_tipe, $keterangan, $link_youtube, $id_akomodasi);

if ($stmt->execute()) {
    $responseData = [
        "id_akomodasi" => $id_akomodasi,
        "id_cabang" => $id_cabang,
        "id_tipe" => $id_tipe,
        "keterangan" => $keterangan,
        "link_youtube" => $link_youtube
    ];

    $response = [
        "success" => true,
        "message" => "Data cabang tipe berhasil diupdate",
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
