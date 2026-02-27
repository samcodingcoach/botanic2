<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    $query = "SELECT
              front_office.id_fo,
              front_office.wa,
              front_office.id_cabang,
              cabang.nama_cabang,
              front_office.aktif
              FROM
              front_office
              INNER JOIN
              cabang
              ON
              front_office.id_cabang = cabang.id_cabang
              ORDER BY front_office.id_fo ASC";

    $result = $conn->query($query);

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data front office berhasil diambil",
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
