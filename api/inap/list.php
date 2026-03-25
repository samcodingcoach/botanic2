<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    $query = "SELECT
        inap.id_inap,
        inap.id_cabang,
        cabang.nama_cabang,
        inap.id_akomodasi,
        tipe_kamar.nama_tipe,
        inap.id_guest,
        guest.nama_lengkap,
        inap.kode_booking,
        inap.nomor_kamar,
        inap.tanggal_in,
        inap.tanggal_out,
        inap.`status`,
        inap.ota,
        inap.link_receipt,
        inap.created_date,
        inap.id_users,
        users.username
        FROM
        inap
        INNER JOIN
        cabang
        ON
        inap.id_cabang = cabang.id_cabang
        INNER JOIN
        cabang_tipe
        ON
        inap.id_akomodasi = cabang_tipe.id_akomodasi
        INNER JOIN
        tipe_kamar
        ON
        cabang_tipe.id_tipe = tipe_kamar.id_tipe
        INNER JOIN
        guest
        ON
        inap.id_guest = guest.id_guest
        INNER JOIN
        users
        ON
        inap.id_users = users.id_users
        ORDER BY inap.id_inap DESC";

    $result = $conn->query($query);

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            // Format dates to show only date part
            $row['tanggal_in'] = date('Y-m-d', strtotime($row['tanggal_in']));
            $row['tanggal_out'] = date('Y-m-d', strtotime($row['tanggal_out']));
            // Convert status to readable format
            $row['status_label'] = $row['status'] == 0 ? 'staying' : 'completed';
            $data[] = $row;
        }

        $response = [
            "success" => true,
            "message" => "Data inap berhasil diambil",
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
