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
            teknisi.id_teknisi,
            teknisi.kode_teknisi,
            teknisi.nama_teknisi,
            teknisi.id_cabang,
            cabang.nama_cabang,
            teknisi.jabatan,
            teknisi.jenis_kelamin,
            teknisi.wa,
            teknisi.aktif,
            teknisi.created_date,
            teknisi.spesialis
        FROM
            teknisi
        INNER JOIN
            cabang
        ON
            teknisi.id_cabang = cabang.id_cabang
        WHERE
            teknisi.id_cabang = ?
        ORDER BY teknisi.id_teknisi ASC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_cabang);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Get all teknisi if no id_cabang specified
        $query = "SELECT
            teknisi.id_teknisi,
            teknisi.kode_teknisi,
            teknisi.nama_teknisi,
            teknisi.id_cabang,
            cabang.nama_cabang,
            teknisi.jabatan,
            teknisi.jenis_kelamin,
            teknisi.wa,
            teknisi.aktif,
            teknisi.created_date,
            teknisi.spesialis
        FROM
            teknisi
        INNER JOIN
            cabang
        ON
            teknisi.id_cabang = cabang.id_cabang
        ORDER BY teknisi.id_teknisi ASC";

        $result = $conn->query($query);
    }

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data teknisi berhasil diambil",
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
