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
        "message" => "Method not allowed. Use POST."
    ];
    echo json_encode($response);
    exit;
}

try {
    // Get POST data
    $id_area = isset($_POST['id_area']) ? (int) $_POST['id_area'] : 0;

    // Validation
    if ($id_area <= 0) {
        throw new Exception("id_area is required");
    }

    // Check if near_area exists and get foto
    $checkArea = $conn->prepare("SELECT id_area, foto FROM near_area WHERE id_area = ?");
    $checkArea->bind_param("i", $id_area);
    $checkArea->execute();
    $result = $checkArea->get_result();
    $areaData = $result->fetch_assoc();
    $checkArea->close();

    if (!$areaData) {
        throw new Exception("Near area not found");
    }

    $foto = $areaData['foto'] ?? '';

    // Delete foto file if exists
    if (!empty($foto)) {
        $fotoPath = __DIR__ . '/../../images/' . $foto;
        if (file_exists($fotoPath)) {
            unlink($fotoPath);
        }
    }

    // Delete near_area
    $query = "DELETE FROM near_area WHERE id_area = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_area);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Data near area dan foto berhasil dihapus",
            "data" => [
                "id_area" => $id_area,
                "foto_deleted" => !empty($foto)
            ]
        ];
    } else {
        throw new Exception("Gagal menghapus data: " . $stmt->error);
    }

    $stmt->close();
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ];
}

echo json_encode($response);
$conn->close();
?>
