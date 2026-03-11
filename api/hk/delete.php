<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// Method validation
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = ["success" => false, "message" => "Method not allowed"];
    echo json_encode($response);
    exit;
}

try {
    // Get JSON input
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Get id_hk from POST or JSON input
    $id_hk = isset($_POST['id_hk']) ? (int) $_POST['id_hk'] : (isset($input['id_hk']) ? (int) $input['id_hk'] : 0);

    // Validate id_hk
    if (empty($id_hk)) {
        $response = ["success" => false, "message" => "ID housekeeping wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Check if data exists
    $checkQuery = "SELECT id_hk, nama_lengkap FROM hk WHERE id_hk = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $id_hk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response = ["success" => false, "message" => "Data housekeeping tidak ditemukan"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    $housekeeping = $result->fetch_assoc();
    $stmt->close();

    // Delete housekeeping
    $deleteQuery = "DELETE FROM hk WHERE id_hk = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id_hk);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Housekeeping berhasil dihapus",
            "data" => [
                "id_hk" => $id_hk,
                "nama_lengkap" => $housekeeping['nama_lengkap']
            ]
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Gagal menghapus data: " . $stmt->error
        ];
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
