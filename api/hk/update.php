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
    $kode_hk = isset($_POST['kode_hk']) ? trim($_POST['kode_hk']) : '';
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
    $nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
    $jenis_kelamin = isset($_POST['jenis_kelamin']) ? (int) $_POST['jenis_kelamin'] : 0;
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;
    $wa = isset($_POST['wa']) ? trim($_POST['wa']) : ''; // Keep as string to preserve leading 0

    // Validate id_hk
    if (empty($id_hk)) {
        $response = ["success" => false, "message" => "ID housekeeping wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Validate required fields
    if (empty($kode_hk)) {
        $response = ["success" => false, "message" => "Kode housekeeping wajib diisi"];
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

    if (empty($nama_lengkap)) {
        $response = ["success" => false, "message" => "Nama lengkap wajib diisi"];
        echo json_encode($response);
        exit;
    }

    if (empty($wa)) {
        $response = ["success" => false, "message" => "WhatsApp wajib diisi"];
        echo json_encode($response);
        exit;
    }

    // Check if data exists
    $checkQuery = "SELECT id_hk FROM hk WHERE id_hk = ?";
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
    $stmt->close();

    // Check duplicate kode_hk (excluding current record)
    $checkDuplicateQuery = "SELECT id_hk FROM hk WHERE kode_hk = ? AND id_hk != ?";
    $stmt = $conn->prepare($checkDuplicateQuery);
    $stmt->bind_param("si", $kode_hk, $id_hk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Kode housekeeping sudah ada, tidak boleh duplikat"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Update housekeeping
    $query = "UPDATE hk SET 
              kode_hk = ?, 
              id_cabang = ?, 
              jabatan = ?, 
              nama_lengkap = ?, 
              jenis_kelamin = ?, 
              wa = ?, 
              aktif = ? 
              WHERE id_hk = ?";
    
    $stmt = $conn->prepare($query);
    // Use 's' for wa to preserve leading 0 (store as string)
    $stmt->bind_param("sissssii", $kode_hk, $id_cabang, $jabatan, $nama_lengkap, $jenis_kelamin, $wa, $aktif, $id_hk);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Housekeeping berhasil diupdate",
            "data" => [
                "id_hk" => $id_hk,
                "kode_hk" => $kode_hk,
                "nama_lengkap" => $nama_lengkap
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
