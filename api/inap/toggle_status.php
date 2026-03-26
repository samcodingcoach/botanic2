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

// Check if id_inap exists and get current status
$checkQuery = "SELECT id_inap, status FROM inap WHERE id_inap = ?";
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
$currentStatus = $oldData['status'];
$stmt->close();

// Toggle status: 0 (Staying) -> 1 (Completed), 1 (Completed) -> 0 (Staying)
$newStatus = $currentStatus == 0 ? 1 : 0;

// Update the status
$updateQuery = "UPDATE inap SET status = ? WHERE id_inap = ?";
$stmt = $conn->prepare($updateQuery);
$stmt->bind_param("ii", $newStatus, $id_inap);

if ($stmt->execute()) {
    $response = [
        "success" => true,
        "message" => "Status berhasil diupdate",
        "data" => [
            "id_inap" => $id_inap,
            "old_status" => $currentStatus,
            "new_status" => $newStatus,
            "status_label" => $newStatus == 0 ? "Staying" : "Completed"
        ]
    ];
} else {
    $response = [
        "success" => false,
        "message" => "Gagal mengupdate status: " . $stmt->error
    ];
}

$stmt->close();
$conn->close();

echo json_encode($response);
?>
