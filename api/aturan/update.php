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
    $id_aturan = isset($_POST['id_aturan']) ? (int) $_POST['id_aturan'] : 0;
    $kategori = isset($_POST['kategori']) ? (int) $_POST['kategori'] : -1;
    $nama_aturan = isset($_POST['nama_aturan']) ? trim($_POST['nama_aturan']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    $denda = isset($_POST['denda']) ? (int) $_POST['denda'] : 0;
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;

    // Validate id_aturan
    if (empty($id_aturan)) {
        $response = ["success" => false, "message" => "ID aturan wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Validate kategori (0, 1, or 2)
    if ($kategori < 0 || $kategori > 2) {
        $response = ["success" => false, "message" => "Kategori harus diisi (0, 1, atau 2)"];
        echo json_encode($response);
        exit;
    }

    // Validate required fields
    if (empty($nama_aturan)) {
        $response = ["success" => false, "message" => "Nama aturan wajib diisi"];
        echo json_encode($response);
        exit;
    }

    if (empty($deskripsi)) {
        $response = ["success" => false, "message" => "Deskripsi wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Check if data exists
    $checkQuery = "SELECT id_aturan FROM aturan WHERE id_aturan = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("i", $id_aturan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $response = ["success" => false, "message" => "Data aturan tidak ditemukan"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Check duplicate nama_aturan in the same category (excluding current record)
    $checkDuplicateQuery = "SELECT id_aturan FROM aturan WHERE nama_aturan = ? AND kategori = ? AND id_aturan != ?";
    $stmt = $conn->prepare($checkDuplicateQuery);
    $stmt->bind_param("sii", $nama_aturan, $kategori, $id_aturan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Nama aturan sudah ada di kategori ini, tidak boleh duplikat"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Update aturan
    $query = "UPDATE aturan SET
              kategori = ?,
              nama_aturan = ?,
              deskripsi = ?,
              denda = ?,
              aktif = ?
              WHERE id_aturan = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issiii", $kategori, $nama_aturan, $deskripsi, $denda, $aktif, $id_aturan);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Data aturan berhasil diupdate",
            "data" => [
                "id_aturan" => $id_aturan,
                "kategori" => $kategori,
                "nama_aturan" => $nama_aturan
            ]
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Gagal mengupdate data: " . $stmt->error
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
