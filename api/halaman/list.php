<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    // Check if id_cabang is provided in GET parameters
    if (isset($_GET['id_cabang']) && !empty($_GET['id_cabang'])) {
        $id_cabang = (int) $_GET['id_cabang'];

        $query = "SELECT
            halaman.id_halaman,
            halaman.id_users,
            users.username,
            halaman.id_cabang,
            cabang.nama_cabang,
            halaman.nama_halaman,
            halaman.link,
            halaman.username as username_halaman,
            halaman.created_date,
            halaman.logo,
            halaman.aktif
        FROM
            halaman
        INNER JOIN
            users
        ON
            halaman.id_users = users.id_users
        INNER JOIN
            cabang
        ON
            halaman.id_cabang = cabang.id_cabang
        WHERE
            halaman.id_cabang = ?
        ORDER BY halaman.id_halaman ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_cabang);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Get all halaman if no id_cabang specified
        $query = "SELECT
            halaman.id_halaman,
            halaman.id_users,
            users.username,
            halaman.id_cabang,
            cabang.nama_cabang,
            halaman.nama_halaman,
            halaman.link,
            halaman.username as username_halaman,
            halaman.created_date,
            halaman.logo,
            halaman.aktif
        FROM
            halaman
        INNER JOIN
            users
        ON
            halaman.id_users = users.id_users
        INNER JOIN
            cabang
        ON
            halaman.id_cabang = cabang.id_cabang
        ORDER BY halaman.id_halaman ASC";

        $result = $conn->query($query);
    }

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data halaman berhasil diambil",
            "data" => $data,
            "count" => count($data)
        ];
    } else {
        $response = [
            "success" => false,
            "message" => "Gagal mengambil data: " . $conn->error
        ];
    }
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ];
}

echo json_encode($response);
$conn->close();
?>
