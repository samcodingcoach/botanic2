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
            cabang_tipe.id_akomodasi,
            cabang_tipe.id_cabang,
            cabang.nama_cabang,
            cabang_tipe.id_tipe,
            tipe_kamar.nama_tipe,
            tipe_kamar.gambar,
            tipe_kamar.keterangan AS keterangan_tipe,
            cabang_tipe.keterangan AS keterangan_akomodasi,
            cabang_tipe.created_date,
            cabang_tipe.link_youtube
        FROM
            cabang_tipe
        INNER JOIN
            cabang
        ON
            cabang_tipe.id_cabang = cabang.id_cabang
        INNER JOIN
            tipe_kamar
        ON
            cabang_tipe.id_tipe = tipe_kamar.id_tipe
        WHERE
            cabang_tipe.id_cabang = ?
        ORDER BY cabang_tipe.id_akomodasi DESC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_cabang);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Get all rooms if no id_cabang specified
        $query = "SELECT
            cabang_tipe.id_akomodasi,
            cabang_tipe.id_cabang,
            cabang.nama_cabang,
            cabang_tipe.id_tipe,
            tipe_kamar.nama_tipe,
            tipe_kamar.gambar,
            tipe_kamar.keterangan AS keterangan_tipe,
            cabang_tipe.keterangan AS keterangan_akomodasi,
            cabang_tipe.created_date,
            cabang_tipe.link_youtube
        FROM
            cabang_tipe
        INNER JOIN
            cabang
        ON
            cabang_tipe.id_cabang = cabang.id_cabang
        INNER JOIN
            tipe_kamar
        ON
            cabang_tipe.id_tipe = tipe_kamar.id_tipe
        ORDER BY cabang_tipe.id_akomodasi DESC";

        $result = $conn->query($query);
    }

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data cabang tipe berhasil diambil",
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
