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
    $kode_teknisi = isset($_POST['kode_teknisi']) ? trim($_POST['kode_teknisi']) : '';
    $nama_teknisi = isset($_POST['nama_teknisi']) ? trim($_POST['nama_teknisi']) : '';
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
    $jenis_kelamin = isset($_POST['jenis_kelamin']) ? (int) $_POST['jenis_kelamin'] : 0;
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;
    $wa = isset($_POST['wa']) ? trim($_POST['wa']) : '';
    $spesialis = isset($_POST['spesialis']) ? trim($_POST['spesialis']) : '';

    // Validate required fields
    if (empty($kode_teknisi)) {
        $response = ["success" => false, "message" => "Kode teknisi wajib diisi"];
        echo json_encode($response);
        exit;
    }

    if (empty($nama_teknisi)) {
        $response = ["success" => false, "message" => "Nama teknisi wajib diisi"];
        echo json_encode($response);
        exit;
    }

    if (empty($id_cabang)) {
        $response = ["success" => false, "message" => "Cabang wajib dipilih"];
        echo json_encode($response);
        exit;
    }

    if (empty($jabatan)) {
        $response = ["success" => false, "message" => "Jabatan wajib diisi"];
        echo json_encode($response);
        exit;
    }

    if (empty($wa)) {
        $response = ["success" => false, "message" => "WhatsApp wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Check duplicate kode_teknisi
    $checkQuery = "SELECT id_teknisi FROM teknisi WHERE kode_teknisi = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $kode_teknisi);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Kode teknisi sudah ada, tidak boleh duplikat"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Insert new teknisi
    $query = "INSERT INTO teknisi (kode_teknisi, nama_teknisi, id_cabang, jabatan, jenis_kelamin, wa, aktif, spesialis)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssisssss", $kode_teknisi, $nama_teknisi, $id_cabang, $jabatan, $jenis_kelamin, $wa, $aktif, $spesialis);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;

        $responseData = [
            "id_teknisi" => $newId,
            "kode_teknisi" => $kode_teknisi,
            "nama_teknisi" => $nama_teknisi
        ];

        $response = [
            "success" => true,
            "message" => "Teknisi berhasil ditambahkan",
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
