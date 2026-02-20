<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    $query = "SELECT id_tipe,nama_tipe,gambar,keterangan from tipe_kamar ORDER BY id_tipe DESC";
    
    $result = $conn->query($query);
    
    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        
        $response = [
            "success" => true,
            "message" => "Data tipe kamar berhasil diambil",
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
