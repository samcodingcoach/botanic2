<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

// Get id_cabang from query parameter
$id_cabang = null;

if (isset($_GET['id_cabang'])) {
    $id_cabang = (int) $_GET['id_cabang'];
}

try {
    if ($id_cabang) {
        // Get tipe kamar for specific cabang
        $query = "SELECT
            cabang_tipe.id_akomodasi,
            cabang_tipe.id_cabang,
            cabang_tipe.id_tipe,
            tipe_kamar.nama_tipe,
            tipe_kamar.gambar,
            tipe_kamar.keterangan,
            cabang_tipe.keterangan,
            cabang_tipe.created_date,
            cabang_tipe.link_youtube
            FROM
            cabang_tipe
            INNER JOIN
            tipe_kamar
            ON
            cabang_tipe.id_tipe = tipe_kamar.id_tipe
            WHERE cabang_tipe.id_cabang = ?
            ORDER BY tipe_kamar.nama_tipe ASC";
        
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_cabang);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        // Get all cabang_tipe
        $query = "SELECT
            cabang_tipe.id_akomodasi,
            cabang_tipe.id_cabang,
            cabang_tipe.id_tipe,
            tipe_kamar.nama_tipe,
            tipe_kamar.gambar,
            tipe_kamar.keterangan,
            cabang_tipe.keterangan,
            cabang_tipe.created_date,
            cabang_tipe.link_youtube
            FROM
            cabang_tipe
            INNER JOIN
            tipe_kamar
            ON
            cabang_tipe.id_tipe = tipe_kamar.id_tipe
            ORDER BY cabang_tipe.id_cabang, tipe_kamar.nama_tipe ASC";
        
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
    
    if (isset($stmt)) {
        $stmt->close();
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
