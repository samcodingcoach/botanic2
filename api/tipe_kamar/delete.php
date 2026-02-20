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

// Support raw JSON body only
$inputData = [];
$rawInput = file_get_contents('php://input');
$inputData = json_decode($rawInput, true);

if (empty($inputData)) {
    $response = [
        "success" => false,
        "message" => "Invalid JSON body"
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

$id_tipe = (int) $inputData['id_tipe'];

// Check if id_tipe exists and get image filename
$checkQuery = "SELECT id_tipe, gambar FROM tipe_kamar WHERE id_tipe = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_tipe);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data tipe kamar tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldGambar = $oldData['gambar'];
$stmt->close();

// Delete record from database
$deleteQuery = "DELETE FROM tipe_kamar WHERE id_tipe = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id_tipe);

if ($stmt->execute()) {
    // Delete image file if exists
    if (!empty($oldGambar)) {
        $uploadDir = __DIR__ . '/../../images/';
        $imagePath = $uploadDir . $oldGambar;
        
        if (file_exists($imagePath)) {
            unlink($imagePath);
        }
    }

    $response = [
        "success" => true,
        "message" => "Data tipe kamar berhasil dihapus",
        "data" => [
            "id_tipe" => $id_tipe
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
