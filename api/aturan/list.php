<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

try {
    // Query to get all rules grouped by category
    $query = "SELECT
        id_aturan,
        kategori,
        nama_aturan,
        deskripsi,
        denda,
        aktif,
        created_date
    FROM
        aturan
    ORDER BY
        kategori ASC,
        id_aturan ASC";

    $result = $conn->query($query);

    if ($result) {
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }

        // Group data by kategori (using numeric keys 0, 1, 2)
        $groupedData = [];
        $countByCategory = [
            '0' => 0,
            '1' => 0,
            '2' => 0
        ];

        foreach ($data as $item) {
            $kategori = $item['kategori'];
            if (!isset($groupedData[$kategori])) {
                $groupedData[$kategori] = [];
            }
            $groupedData[$kategori][] = $item;
            $countByCategory[$kategori]++;
        }

        // Ensure all categories exist even if empty
        if (!isset($groupedData[0])) $groupedData[0] = [];
        if (!isset($groupedData[1])) $groupedData[1] = [];
        if (!isset($groupedData[2])) $groupedData[2] = [];

        $response = [
            "success" => true,
            "message" => "Data aturan berhasil diambil",
            "data" => $groupedData,
            "count" => 3,
            "count_by_category" => $countByCategory,
            "categories" => [
                [
                    "key" => 0,
                    "label" => "Ketentuan Check-in & Check-out",
                    "count" => $countByCategory['0']
                ],
                [
                    "key" => 1,
                    "label" => "Denda & Biaya Tambahan",
                    "count" => $countByCategory['1']
                ],
                [
                    "key" => 2,
                    "label" => "Larangan Keras (Tanpa Toleransi)",
                    "count" => $countByCategory['2']
                ]
            ]
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
