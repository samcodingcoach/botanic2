<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

require_once __DIR__ . '/../../config/koneksi.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    $response = [
        "success" => false,
        "message" => "Method not allowed. Use GET method."
    ];
    echo json_encode($response);
    exit;
}

try {
    // Query to get all guest data
    $query = "SELECT 
        id_guest,
        nama_lengkap,
        email,
        wa,
        kota,
        aktif,
        total_point,
        created_at,
        last_login
    FROM guest
    ORDER BY created_at DESC";
    
    $result = $conn->query($query);
    
    if (!$result) {
        throw new Exception("Query failed: " . $conn->error);
    }
    
    $guestList = [];
    while ($row = $result->fetch_assoc()) {
        $guestList[] = [
            "id_guest" => (int) $row['id_guest'],
            "nama_lengkap" => $row['nama_lengkap'],
            "email" => $row['email'],
            "wa" => $row['wa'],
            "kota" => $row['kota'],
            "aktif" => (int) $row['aktif'],
            "total_point" => (float) $row['total_point'],
            "created_at" => $row['created_at'],
            "last_login" => $row['last_login']
        ];
    }
    
    $response = [
        "success" => true,
        "message" => "Data guest berhasil diambil",
        "data" => $guestList,
        "count" => count($guestList)
    ];
    
    echo json_encode($response);
    
} catch (Exception $e) {
    $response = [
        "success" => false,
        "message" => "Terjadi kesalahan: " . $e->getMessage()
    ];
    echo json_encode($response);
}

$conn->close();
?>
