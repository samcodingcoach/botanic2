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
            hk.id_hk,
            hk.kode_hk,
            cabang.nama_cabang,
            hk.id_cabang,
            hk.jabatan,
            hk.nama_lengkap,
            hk.jenis_kelamin,
            hk.wa,
            hk.aktif,
            hk.created_date
        FROM
            cabang
        INNER JOIN
            hk
        ON
            cabang.id_cabang = hk.id_cabang
        WHERE
            hk.id_cabang = ?
        ORDER BY hk.id_hk ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_cabang);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Get all housekeeping if no id_cabang specified
        $query = "SELECT
            hk.id_hk,
            hk.kode_hk,
            cabang.nama_cabang,
            hk.id_cabang,
            hk.jabatan,
            hk.nama_lengkap,
            hk.jenis_kelamin,
            hk.wa,
            hk.aktif,
            hk.created_date
        FROM
            cabang
        INNER JOIN
            hk
        ON
            cabang.id_cabang = hk.id_cabang
        ORDER BY hk.id_hk ASC";

        $result = $conn->query($query);
    }

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data housekeeping berhasil diambil",
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
