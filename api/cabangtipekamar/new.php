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

// Validate required field: id_cabang
if (!isset($inputData['id_cabang']) || empty($inputData['id_cabang'])) {
    $response = [
        "success" => false,
        "message" => "id_cabang wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

// Validate required field: id_tipe
if (!isset($inputData['id_tipe']) || empty($inputData['id_tipe'])) {
    $response = [
        "success" => false,
        "message" => "id_tipe wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_cabang = (int) $inputData['id_cabang'];
$id_tipe = (int) $inputData['id_tipe'];

// Check for duplicate combination
$checkQuery = "SELECT id_akomodasi FROM cabang_tipe WHERE id_cabang = ? AND id_tipe = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("ii", $id_cabang, $id_tipe);
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

// Prepare insert query (exclude id_akomodasi and created_date - auto generated)
$query = "INSERT INTO cabang_tipe (id_cabang, id_tipe, keterangan, link_youtube)
          VALUES (?, ?, ?, ?)";

$stmt = $conn->prepare($query);

$keterangan = isset($inputData['keterangan']) ? trim($inputData['keterangan']) : null;
$link_youtube = isset($inputData['link_youtube']) ? trim($inputData['link_youtube']) : null;

$stmt->bind_param("iiss", $id_cabang, $id_tipe, $keterangan, $link_youtube);

if ($stmt->execute()) {
    $newId = $conn->insert_id;

    $responseData = [
        "id_akomodasi" => $newId,
        "id_cabang" => $id_cabang,
        "id_tipe" => $id_tipe,
        "keterangan" => $keterangan,
        "link_youtube" => $link_youtube
    ];

    $response = [
        "success" => true,
        "message" => "Data cabang tipe berhasil ditambahkan",
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
