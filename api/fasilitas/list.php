<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    $query = "SELECT
        f.id_fasilitas,
        f.id_cabang,
        c.nama_cabang,
        f.nama_fasilitas,
        f.deskripsi,
        f.gambar1,
        f.gambar2,
        f.aktif,
        f.status_free,
        f.range_harga,
        f.created_at
    FROM
        fasilitas f
    INNER JOIN
        cabang c
    ON
        f.id_cabang = c.id_cabang
    ORDER BY f.id_fasilitas DESC";

    $result = $conn->query($query);

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data fasilitas berhasil diambil",
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
