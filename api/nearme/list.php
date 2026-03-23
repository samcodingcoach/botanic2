<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    // Check if aktif parameter is provided
    $aktifFilter = isset($_GET['aktif']) ? (int) $_GET['aktif'] : null;
    
    // Check if id_cabang is provided in GET parameters
    if (isset($_GET['id_cabang']) && !empty($_GET['id_cabang'])) {
        $id_cabang = (int) $_GET['id_cabang'];

        if ($aktifFilter !== null) {
            $query = "SELECT
                near_area.id_area,
                near_area.id_cabang,
                cabang.nama_cabang,
                near_area.nama_area,
                near_area.jenis_area,
                near_area.alamat,
                near_area.gps,
                near_area.jarak,
                near_area.foto,
                near_area.aktif,
                near_area.created_date
            FROM
                near_area
            INNER JOIN
                cabang
            ON
                near_area.id_cabang = cabang.id_cabang
            WHERE
                near_area.id_cabang = ? AND near_area.aktif = ?
            ORDER BY near_area.id_area ASC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("ii", $id_cabang, $aktifFilter);
        } else {
            $query = "SELECT
                near_area.id_area,
                near_area.id_cabang,
                cabang.nama_cabang,
                near_area.nama_area,
                near_area.jenis_area,
                near_area.alamat,
                near_area.gps,
                near_area.jarak,
                near_area.foto,
                near_area.aktif,
                near_area.created_date
            FROM
                near_area
            INNER JOIN
                cabang
            ON
                near_area.id_cabang = cabang.id_cabang
            WHERE
                near_area.id_cabang = ?
            ORDER BY near_area.id_area ASC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $id_cabang);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Get all near_area if no id_cabang specified
        if ($aktifFilter !== null) {
            $query = "SELECT
                near_area.id_area,
                near_area.id_cabang,
                cabang.nama_cabang,
                near_area.nama_area,
                near_area.jenis_area,
                near_area.alamat,
                near_area.gps,
                near_area.jarak,
                near_area.foto,
                near_area.aktif,
                near_area.created_date
            FROM
                near_area
            INNER JOIN
                cabang
            ON
                near_area.id_cabang = cabang.id_cabang
            WHERE
                near_area.aktif = ?
            ORDER BY near_area.id_area ASC";

            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $aktifFilter);
            $stmt->execute();
            $result = $stmt->get_result();
            $stmt->close();
        } else {
            $query = "SELECT
                near_area.id_area,
                near_area.id_cabang,
                cabang.nama_cabang,
                near_area.nama_area,
                near_area.jenis_area,
                near_area.alamat,
                near_area.gps,
                near_area.jarak,
                near_area.foto,
                near_area.aktif,
                near_area.created_date
            FROM
                near_area
            INNER JOIN
                cabang
            ON
                near_area.id_cabang = cabang.id_cabang
            ORDER BY near_area.id_area ASC";

            $result = $conn->query($query);
        }
    }

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data near area berhasil diambil",
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
