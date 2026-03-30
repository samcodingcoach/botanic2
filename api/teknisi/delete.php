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

    // Get id_teknisi from POST or JSON input
    $id_teknisi = isset($_POST['id_teknisi']) ? (int) $_POST['id_teknisi'] : (isset($input['id_teknisi']) ? (int) $input['id_teknisi'] : 0);

    // Validate id_teknisi
    if (empty($id_teknisi)) {
        $response = ["success" => false, "message" => "ID teknisi wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Check if data exists
    $checkQuery = "SELECT id_teknisi, nama_teknisi FROM teknisi WHERE id_teknisi = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $id_teknisi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response = ["success" => false, "message" => "Data teknisi tidak ditemukan"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }

    $teknisi = $result->fetch_assoc();
    $stmt->close();

    // Delete teknisi
    $deleteQuery = "DELETE FROM teknisi WHERE id_teknisi = ?";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bind_param("i", $id_teknisi);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Teknisi berhasil dihapus",
            "data" => [
                "id_teknisi" => $id_teknisi,
                "nama_teknisi" => $teknisi['nama_teknisi']
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
