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
    $id_users = isset($_POST['id_users']) ? (int) $_POST['id_users'] : 0;
    $id_cabang = isset($_POST['id_cabang']) ? (int) $_POST['id_cabang'] : 0;
    $nama_halaman = isset($_POST['nama_halaman']) ? trim($_POST['nama_halaman']) : '';
    $link = isset($_POST['link']) ? trim($_POST['link']) : '';
    $username_halaman = isset($_POST['username_halaman']) ? trim($_POST['username_halaman']) : '';
    $aktif = isset($_POST['aktif']) ? (int) $_POST['aktif'] : 1;

    // Validation
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
    $logo = '';
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
            $logo = 'halaman/' . $logoFilename;
        } else {
            throw new Exception("Failed to upload logo");
        }
    }

    // Insert new halaman
    $query = "INSERT INTO halaman (id_users, id_cabang, nama_halaman, link, username, logo, aktif) 
              VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("iissssi", $id_users, $id_cabang, $nama_halaman, $link, $username_halaman, $logo, $aktif);

    if ($stmt->execute()) {
        $newId = $conn->insert_id;

        $response = [
            "success" => true,
            "message" => "Data halaman berhasil ditambahkan",
            "data" => [
                "id_halaman" => $newId,
                "id_users" => $id_users,
                "id_cabang" => $id_cabang,
                "nama_halaman" => $nama_halaman,
                "link" => $link,
                "username_halaman" => $username_halaman,
                "logo" => $logo,
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
