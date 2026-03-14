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
    $id_halaman = isset($_POST['id_halaman']) ? (int) $_POST['id_halaman'] : 0;

    // Validation
    if ($id_halaman <= 0) {
        throw new Exception("id_halaman is required");
    }

    // Check if halaman exists and get logo
    $checkHalaman = $conn->prepare("SELECT id_halaman, logo FROM halaman WHERE id_halaman = ?");
    $checkHalaman->bind_param("i", $id_halaman);
    $checkHalaman->execute();
    $result = $checkHalaman->get_result();
    $halamanData = $result->fetch_assoc();
    $checkHalaman->close();

    if (!$halamanData) {
        throw new Exception("Halaman not found");
    }

    $logo = $halamanData['logo'];

    // Delete logo file if exists
    if (!empty($logo)) {
        $logoPath = __DIR__ . '/../../images/' . $logo;
        if (file_exists($logoPath)) {
            unlink($logoPath);
        }
    }

    // Delete halaman
    $query = "DELETE FROM halaman WHERE id_halaman = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id_halaman);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Data halaman dan logo berhasil dihapus",
            "data" => [
                "id_halaman" => $id_halaman,
                "logo_deleted" => !empty($logo)
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
