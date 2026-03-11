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
    $kode_hk = isset($_POST['kode_hk']) ? trim($_POST['kode_hk']) : '';
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $jabatan = isset($_POST['jabatan']) ? trim($_POST['jabatan']) : '';
    $nama_lengkap = isset($_POST['nama_lengkap']) ? trim($_POST['nama_lengkap']) : '';
    $jenis_kelamin = isset($_POST['jenis_kelamin']) ? (int) $_POST['jenis_kelamin'] : 0;
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;
    $wa = isset($_POST['wa']) ? trim($_POST['wa']) : ''; // Keep as string to preserve leading 0

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

    // Check duplicate kode_hk
    $checkQuery = "SELECT id_hk FROM hk WHERE kode_hk = ?";
    $stmt = $conn->prepare($checkQuery);
    $stmt->bind_param("s", $kode_hk);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $response = ["success" => false, "message" => "Kode housekeeping sudah ada, tidak boleh duplikat"];
        $stmt->close();
        echo json_encode($response);
        exit;
    }
    $stmt->close();

    // Insert new housekeeping
    $query = "INSERT INTO hk (kode_hk, id_cabang, jabatan, nama_lengkap, jenis_kelamin, wa, aktif, created_date) 
              VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($query);
    // Use 's' for wa to preserve leading 0 (store as string)
    $stmt->bind_param("sissssi", $kode_hk, $id_cabang, $jabatan, $nama_lengkap, $jenis_kelamin, $wa, $aktif);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;

        $responseData = [
            "id_hk" => $newId,
            "kode_hk" => $kode_hk,
            "nama_lengkap" => $nama_lengkap
        ];

        $response = [
            "success" => true,
            "message" => "Housekeeping berhasil ditambahkan",
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
