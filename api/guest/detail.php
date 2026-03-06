<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");

session_start();

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

// Check if guest is logged in
if (!isset($_SESSION['id_guest'])) {
    $response = [
        "success" => false,
        "message" => "Unauthorized. Please login first."
    ];
    echo json_encode($response);
    exit;
}

$id_guest = (int) $_SESSION['id_guest'];

// Fetch guest data
$query = "SELECT id_guest, nama_lengkap, email, wa, kota, total_point, aktif, created_at, last_login
          FROM guest
          WHERE id_guest = ?";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_guest);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $response = [
        "success" => false,
        "message" => "Guest data not found"
    ];
    $stmt->close();
    echo json_encode($response);
    exit;
}

$guest = $result->fetch_assoc();
$stmt->close();

$response = [
    "success" => true,
    "message" => "Guest data retrieved successfully",
    "data" => [
        "id_guest" => (int) $guest['id_guest'],
        "nama_lengkap" => $guest['nama_lengkap'],
        "email" => $guest['email'],
        "wa" => $guest['wa'],
        "kota" => $guest['kota'],
        "total_point" => (float) $guest['total_point'],
        "aktif" => (int) $guest['aktif'],
        "created_at" => $guest['created_at'],
        "last_login" => $guest['last_login']
    ]
];

echo json_encode($response);
$conn->close();
?>
