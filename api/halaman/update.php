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
    $id_halaman = isset($_POST['id_halaman']) ? (int) $_POST['id_halaman'] : 0;
    $id_users = isset($_POST['id_users']) ? (int) $_POST['id_users'] : 0;
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $nama_halaman = isset($_POST['nama_halaman']) ? trim($_POST['nama_halaman']) : '';
    $link = isset($_POST['link']) ? trim($_POST['link']) : '';
    $username_halaman = isset($_POST['username_halaman']) ? trim($_POST['username_halaman']) : '';
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;
    $created_date = isset($_POST['created_date']) ? trim($_POST['created_date']) : date('Y-m-d H:i:s');

    // Validation
    if ($id_halaman <= 0) {
        throw new Exception("id_halaman is required");
    }
    if ($id_users <= 0) {
        throw new Exception("id_users is required");
    }
    if ($id_cabang <= 0) {
        throw new Exception("id_cabang is required");
    }
    if (empty($nama_halaman)) {
        throw new Exception("nama_halaman is required");
    }
    if (empty($link)) {
        throw new Exception("link is required");
    }

    // Check if halaman exists
    $checkHalaman = $conn->prepare("SELECT id_halaman, logo FROM halaman WHERE id_halaman = ?");
    $checkHalaman->bind_param("i", $id_halaman);
    $checkHalaman->execute();
    $halamanResult = $checkHalaman->get_result();
    $halamanData = $checkHalaman->get_result()->fetch_assoc();
    $checkHalaman->close();

    if (!$halamanData) {
        throw new Exception("Halaman not found");
    }

    $existingLogo = $halamanData['logo'] ?? '';

    // Check if users exists
    $checkUser = $conn->prepare("SELECT id_users FROM users WHERE id_users = ?");
    $checkUser->bind_param("i", $id_users);
    $checkUser->execute();
    $userResult = $checkUser->get_result();
    $checkUser->close();

    if ($userResult->num_rows === 0) {
        throw new Exception("User not found");
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

    // Handle logo file upload
    $logo = $existingLogo;
    if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../../images/halaman/';
        
        // Create directory if not exists
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $fileExtension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($fileExtension, $allowedExtensions)) {
            throw new Exception("Invalid file type. Allowed: " . implode(', ', $allowedExtensions));
        }

        // Generate unique filename
        $logoFilename = 'logo_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $uploadPath = $uploadDir . $logoFilename;

        if (move_uploaded_file($_FILES['logo']['tmp_name'], $uploadPath)) {
            // Delete old logo if exists
            if (!empty($existingLogo)) {
                $oldLogoPath = __DIR__ . '/../../images/' . $existingLogo;
                if (file_exists($oldLogoPath)) {
                    unlink($oldLogoPath);
                }
            }
            $logo = 'halaman/' . $logoFilename;
        } else {
            throw new Exception("Failed to upload logo");
        }
    }

    // Update halaman
    $query = "UPDATE halaman 
              SET id_users = ?, id_cabang = ?, nama_halaman = ?, link = ?, username = ?, logo = ?, aktif = ?, created_date = ?
              WHERE id_halaman = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iisssssii", $id_users, $id_cabang, $nama_halaman, $link, $username_halaman, $logo, $aktif, $created_date, $id_halaman);

    if ($stmt->execute()) {
        $response = [
            "success" => true,
            "message" => "Data halaman berhasil diupdate",
            "data" => [
                "id_halaman" => $id_halaman,
                "id_users" => $id_users,
                "id_cabang" => $id_cabang,
                "nama_halaman" => $nama_halaman,
                "link" => $link,
                "username_halaman" => $username_halaman,
                "logo" => $logo,
                "aktif" => $aktif,
                "created_date" => $created_date
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
