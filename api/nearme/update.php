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
    $id_area = isset($_POST['id_area']) ? (int) $_POST['id_area'] : 0;
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $nama_area = isset($_POST['nama_area']) ? trim($_POST['nama_area']) : '';
    $jenis_area = isset($_POST['jenis_area']) ? trim($_POST['jenis_area']) : '';
    $alamat = isset($_POST['alamat']) ? trim($_POST['alamat']) : '';
    $gps = isset($_POST['gps']) ? trim($_POST['gps']) : '';
    $jarak = isset($_POST['jarak']) ? trim($_POST['jarak']) : '';
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;

    // Validation
    if ($id_area <= 0) {
        throw new Exception("id_area is required");
    }
    if ($id_cabang <= 0) {
        throw new Exception("id_cabang is required");
    }
    if (empty($nama_area)) {
        throw new Exception("nama_area is required");
    }
    if (empty($jenis_area)) {
        throw new Exception("jenis_area is required");
    }

    // Check if near_area exists and get existing foto
    $checkArea = $conn->prepare("SELECT id_area, foto FROM near_area WHERE id_area = ?");
    $checkArea->bind_param("i", $id_area);
    $checkArea->execute();
    $areaResult = $checkArea->get_result();
    $areaData = $areaResult->fetch_assoc();
    $checkArea->close();

    if (!$areaData) {
        throw new Exception("Near area not found");
    }

    $existingFoto = $areaData['foto'] ?? '';

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
    $foto = $existingFoto;
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
            // Delete old foto if exists
            if (!empty($existingFoto)) {
                $oldFotoPath = __DIR__ . '/../../images/' . $existingFoto;
                if (file_exists($oldFotoPath)) {
                    unlink($oldFotoPath);
                }
            }
            $foto = 'near/' . $fotoFilename;
        } else {
            throw new Exception("Failed to upload foto");
        }
    }

    // Update near_area
    $query = "UPDATE near_area
              SET id_cabang = ?, nama_area = ?, jenis_area = ?, alamat = ?, gps = ?, jarak = ?, foto = ?, aktif = ?
              WHERE id_area = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("isssssssi", $id_cabang, $nama_area, $jenis_area, $alamat, $gps, $jarak, $foto, $aktif, $id_area);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Data near area berhasil diupdate",
            "data" => [
                "id_area" => $id_area,
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
        throw new Exception("Gagal mengupdate data: " . $stmt->error);
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
