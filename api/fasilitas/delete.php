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

// Validate required field: id_fasilitas
if (!isset($inputData['id_fasilitas']) || empty($inputData['id_fasilitas'])) {
    $response = [
        "success" => false,
        "message" => "id_fasilitas wajib diisi"
    ];
    echo json_encode($response);
    exit;
}

$id_fasilitas = (int) $inputData['id_fasilitas'];

// Check if id_fasilitas exists and get image filenames
$checkQuery = "SELECT id_fasilitas, gambar1, gambar2 FROM fasilitas WHERE id_fasilitas = ?";
$stmt = $conn->prepare($checkQuery);
$stmt->bind_param("i", $id_fasilitas);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Data fasilitas tidak ditemukan"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$oldData = $result->fetch_assoc();
$oldGambar1 = $oldData['gambar1'];
$oldGambar2 = $oldData['gambar2'];
$stmt->close();

// Delete record from database
$deleteQuery = "DELETE FROM fasilitas WHERE id_fasilitas = ?";
$stmt = $conn->prepare($deleteQuery);
$stmt->bind_param("i", $id_fasilitas);

if ($stmt->execute()) {
    // Delete image files if exist
    $uploadDir = __DIR__ . '/../../images/';
    
    if (!empty($oldGambar1)) {
        $imagePath1 = $uploadDir . $oldGambar1;
        if (file_exists($imagePath1)) {
            unlink($imagePath1);
        }
    }
    
    if (!empty($oldGambar2)) {
        $imagePath2 = $uploadDir . $oldGambar2;
        if (file_exists($imagePath2)) {
            unlink($imagePath2);
        }
    }

    $response = [
        "success" => true,
        "message" => "Data fasilitas berhasil dihapus",
        "data" => [
            "id_fasilitas" => $id_fasilitas
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
