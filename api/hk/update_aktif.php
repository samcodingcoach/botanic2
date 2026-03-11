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
    // Get form data
    $id_hk = isset($_POST['id_hk']) ? (int) $_POST['id_hk'] : 0;
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 0;

    // Validate id_hk
    if (empty($id_hk)) {
        $response = ["success" => false, "message" => "ID housekeeping wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Validate aktif value (must be 0 or 1)
    if ($aktif !== 0 && $aktif !== 1) {
        $response = ["success" => false, "message" => "Status aktif harus 0 atau 1"];
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

    // Update aktif status
    $updateQuery = "UPDATE hk SET aktif = ? WHERE id_hk = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ii", $aktif, $id_hk);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Status berhasil diubah",
            "data" => [
                "id_hk" => $id_hk,
                "nama_lengkap" => $housekeeping['nama_lengkap'],
                "aktif" => $aktif
            ]
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Gagal mengubah status: " . $stmt->error
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
