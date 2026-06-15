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

// Ambil data file dari database [cite: 30]
$query = "SELECT * FROM files WHERE id = '$file_id' AND status = 'active'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    catatLog($conn, $username, strtoupper($role), 'DOWNLOAD', 'File ID ' . $file_id, 'GAGAL (File tidak ditemukan)');
    echo "<script>alert('File tidak ditemukan atau sudah dihapus.'); window.location.href='../dashboard.php';</script>";
    exit;
}

$file = $result->fetch_assoc();

// Validasi Hak Akses (User hanya unduh file sendiri, Viewer ditolak) [cite: 30, 144]
if ($role === 'user' && $file['user_id'] != $user_id) {
    catatLog($conn, $username, strtoupper($role), 'DOWNLOAD', $file['original_name'], 'DITOLAK (Bukan pemilik file)');
    echo "<script>alert('Akses ditolak! Anda hanya bisa mengunduh file milik sendiri.'); window.location.href='../dashboard.php';</script>";
    exit;
} else if ($role === 'viewer') {
    catatLog($conn, $username, strtoupper($role), 'DOWNLOAD', $file['original_name'], 'DITOLAK (Akses Viewer dibatasi)');
    echo "<script>alert('Viewer tidak memiliki izin untuk mengunduh file ini.'); window.location.href='../dashboard.php';</script>";
    exit;
}

// Tentukan letak file fisik
$filepath = '../uploads/' . $file['filename'];

if (file_exists($filepath)) {
    // Catat log keberhasilan SEBELUM proses download dimulai [cite: 31]
    catatLog($conn, $username, strtoupper($role), 'DOWNLOAD', $file['original_name'], 'BERHASIL');

    // Manipulasi Header HTTP agar file terunduh dengan baik dan tidak rusak [cite: 28, 29]
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . basename($file['original_name']) . '"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($filepath));
    
    // Baca dan kirim file ke browser
    readfile($filepath);
    exit;
} else {
    catatLog($conn, $username, strtoupper($role), 'DOWNLOAD', $file['original_name'], 'GAGAL (File fisik server hilang)');
    echo "<script>alert('File fisik tidak ditemukan di server.'); window.location.href='../dashboard.php';</script>";
}
?>