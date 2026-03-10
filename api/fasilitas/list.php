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
        WHERE
            f.id_cabang = ?
        ORDER BY f.id_fasilitas DESC";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $id_cabang);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
    } else {
        // Get all facilities if no id_cabang specified
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
    }

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
