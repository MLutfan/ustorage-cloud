<?php
// Ganti kredensial di bawah ini sesuai dengan database Anda, 
// lalu ubah nama file ini menjadi database.php
$host = "localhost";
$username = "root";
$password = ""; 
$dbname = "ustorage";

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Koneksi Database Gagal: " . $conn->connect_error);
}
?>