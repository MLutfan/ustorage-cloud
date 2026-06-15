<?php
session_start();
require_once '../config/database.php';
require_once 'logger.php'; // Memanggil fungsi log

// Pengecekan sesi dan keberadaan parameter ID file
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$file_id = intval($_GET['id']);

// Ambil data file dari database
$query = "SELECT * FROM files WHERE id = '$file_id' AND status = 'active'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    catatLog($conn, $username, strtoupper($role), 'VIEW', 'File ID ' . $file_id, 'GAGAL (File tidak ditemukan)');
    echo "<script>alert('File tidak ditemukan atau sudah dipindahkan ke trash.'); window.close();</script>";
    exit;
}

$file = $result->fetch_assoc();

// Validasi Hak Akses (User hanya boleh melihat file miliknya sendiri)
if ($role === 'user' && $file['user_id'] != $user_id) {
    catatLog($conn, $username, strtoupper($role), 'VIEW', $file['original_name'], 'DITOLAK (Bukan pemilik)');
    echo "<script>alert('Akses ditolak! Anda hanya bisa melihat file milik sendiri.'); window.close();</script>";
    exit;
}

// Tentukan letak file fisik di server
$filepath = '../uploads/' . $file['filename'];

if (file_exists($filepath)) {
    // Catat log keberhasilan SEBELUM file dirender
    catatLog($conn, $username, strtoupper($role), 'VIEW', $file['original_name'], 'BERHASIL');

    // Mendapatkan MIME type dari file fisik (misal: image/jpeg, application/pdf)
    $mime_type = mime_content_type($filepath);

    // Manipulasi Header HTTP agar file dirender di browser (bukan di-download)
    header('Content-Type: ' . $mime_type);
    header('Content-Disposition: inline; filename="' . basename($file['original_name']) . '"');
    header('Content-Transfer-Encoding: binary');
    header('Accept-Ranges: bytes');
    
    // Baca dan tampilkan file
    readfile($filepath);
    exit;
} else {
    catatLog($conn, $username, strtoupper($role), 'VIEW', $file['original_name'], 'GAGAL (File fisik server hilang)');
    echo "<script>alert('File fisik tidak ditemukan di server.'); window.close();</script>";
}
?>