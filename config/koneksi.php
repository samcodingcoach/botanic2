<?php
$host = 'localhost';
$username = 'matos';
$password = '1234';
$database = 'botanic2';

date_default_timezone_set("Asia/Makassar");

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Koneksi Gagal: " . $conn->connect_error);
}
?>