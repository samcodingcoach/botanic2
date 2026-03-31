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
    $kategori = isset($_POST['kategori']) ? (int) $_POST['kategori'] : -1;
    $nama_aturan = isset($_POST['nama_aturan']) ? trim($_POST['nama_aturan']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    $denda = isset($_POST['denda']) ? (int) $_POST['denda'] : 0;
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;

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

    // Check duplicate nama_aturan in the same category
    $checkQuery = "SELECT id_aturan FROM aturan WHERE nama_aturan = ? AND kategori = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("ii", $nama_aturan, $kategori);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Nama aturan sudah ada di kategori ini, tidak boleh duplikat"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Insert new aturan
    $query = "INSERT INTO aturan (kategori, nama_aturan, deskripsi, denda, aktif, created_date)
              VALUES (?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issii", $kategori, $nama_aturan, $deskripsi, $denda, $aktif);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;

        $responseData = [
            "id_aturan" => $newId,
            "kategori" => $kategori,
            "nama_aturan" => $nama_aturan
        ];

        $response = [
            "success" => true,
            "message" => "Data aturan berhasil ditambahkan",
            "data" => $responseData
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Gagal menyimpan data: " . $stmt->error
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
