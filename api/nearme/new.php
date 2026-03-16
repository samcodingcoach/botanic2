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
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $nama_area = isset($_POST['nama_area']) ? trim($_POST['nama_area']) : '';
    $jenis_area = isset($_POST['jenis_area']) ? trim($_POST['jenis_area']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $gps = isset($_POST['gps']) ? trim($_POST['gps']) : '';
    $jarak = isset($_POST['jarak']) ? trim($_POST['jarak']) : '';
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;

    // Validation
    if ($id_cabang <= 0) {
        throw new Exception("id_cabang is required");
    }
    if (empty($nama_area)) {
        throw new Exception("nama_area is required");
    }
    if (empty($jenis_area)) {
        throw new Exception("jenis_area is required");
    }

    // Check if cabang exists
    $checkCabang = $conn->prepare("SELECT id_cabang FROM cabang WHERE id_cabang = ?");
    $checkCabang->bind_param("i", $id_cabang);
    $checkCabang->execute();
    $cabangResult = $checkCabang->get_result();
    $checkCabang->close();

    if ($cabangResult->num_rows === 0) {
        throw new Exception("Cabang not found");
    }

    // Handle foto file upload
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../images/near/';

        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowedExtensions));
        }

        // Generate unique filename
        $fotoFilename = 'near_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $fotoFilename;

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $uploadPath)) {
            $foto = 'near/' . $fotoFilename;
        } else {
            throw new Exception("Failed to upload foto");
        }
    }

    // Insert new near_area
    $query = "INSERT INTO near_area (id_cabang, nama_area, jenis_area, alamat, gps, jarak, foto, aktif, created_date)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("issssssi", $id_cabang, $nama_area, $jenis_area, $alamat, $gps, $jarak, $foto, $aktif);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;

        $response = [
            "success" => true,
            "message" => "Data near area berhasil ditambahkan",
            "data" => [
                "id_area" => $newId,
                "id_cabang" => $id_cabang,
                "nama_area" => $nama_area,
                "jenis_area" => $jenis_area,
                "alamat" => $alamat,
                "gps" => $gps,
                "jarak" => $jarak,
                "foto" => $foto,
                "aktif" => $aktif
            ]
        ];
    } else {
        throw new Exception("Gagal menambahkan data: " . $stmt->error);
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
