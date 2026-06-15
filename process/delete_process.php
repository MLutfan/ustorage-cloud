<?php
session_start();
require_once '../config/database.php';
require_once 'logger.php';

// Pengecekan sesi
if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];
$file_id = intval($_GET['id']);

// Keamanan: Viewer dilarang keras melakukan penghapusan [cite: 35]
if ($role === 'viewer') {
    catatLog($conn, $username, strtoupper($role), 'DELETE', 'Mencoba akses delete.php', 'DITOLAK');
    header("Location: ../dashboard.php");
    exit;
}

// Ambil data file
$query = "SELECT * FROM files WHERE id = '$file_id' AND status = 'active'";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $file = $result->fetch_assoc();

    // Keamanan: User biasa tidak bisa menghapus file milik user lain [cite: 34, 45]
    if ($role === 'user' && $file['user_id'] != $user_id) {
        catatLog($conn, $username, strtoupper($role), 'DELETE', $file['original_name'], 'DITOLAK (Bukan pemilik file)');
        header("Location: ../dashboard.php");
        exit;
    }

    // Tentukan letak file awal dan tujuan pemindahan
    $source = '../uploads/' . $file['filename'];
    $destination = '../trash/' . $file['filename']; // 

    // Pindahkan file ke folder trash secara fisik
    if (rename($source, $destination)) {
        // Jika berhasil pindah, ubah status di database
        $conn->query("UPDATE files SET status = 'trash' WHERE id = '$file_id'");
        catatLog($conn, $username, strtoupper($role), 'DELETE', $file['original_name'], 'BERHASIL'); // [cite: 38]
    } else {
        catatLog($conn, $username, strtoupper($role), 'DELETE', $file['original_name'], 'GAGAL (Server Error rename)');
    }
}

// Kembalikan ke dashboard secara diam-diam tanpa pesan alert karena SweetAlert konfirmasi sudah berjalan sebelumnya
header("Location: ../dashboard.php");
exit;
?>