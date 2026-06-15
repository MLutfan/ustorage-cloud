<?php
session_start();
require_once '../config/database.php';
require_once 'logger.php'; // Memanggil fungsi log

// =========================================================================
// FUNGSI NOTIFIKASI SWEETALERT (Sama seperti di auth)
// =========================================================================
function showSweetAlert($title, $text, $icon, $redirect) {
    echo "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>$title</title><link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'><script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script><style>body { background-color: #121212; font-family: 'Poppins', sans-serif; margin: 0; display: flex; align-items: center; justify-content: center; height: 100vh; } .swal2-popup { border-radius: 1rem !important; }</style></head><body><script>Swal.fire({ title: '$title', text: '$text', icon: '$icon', background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#A3E635', confirmButtonText: '<span style=\"color:#000; font-weight:600; padding: 0 15px;\">Oke</span>', allowOutsideClick: false, customClass: { confirmButton: 'rounded-xl' } }).then((result) => { if (result.isConfirmed) { window.location.href = '$redirect'; } });</script></body></html>";
    exit;
}

// 1. PENGECEKAN KEAMANAN AKSES
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Viewer dilarang keras melakukan upload
if ($role === 'viewer') {
    // Catat log aktivitas percobaan akses ilegal
    catatLog($conn, $username, strtoupper($role), 'UPLOAD', 'Mencoba akses upload.php', 'DITOLAK');
    showSweetAlert('Akses Ditolak!', 'Viewer tidak diizinkan mengunggah file.', 'error', '../dashboard.php');
}

// 2. PROSES UPLOAD FILE
if (isset($_POST['btn_upload'])) {
    
    // Informasi file dari form
    $original_name = $_FILES['file_upload']['name'];
    $file_size = $_FILES['file_upload']['size'];
    $tmp_name = $_FILES['file_upload']['tmp_name'];
    $error = $_FILES['file_upload']['error'];
    
    // Validasi 1: Cek apakah ada error saat upload (misal koneksi putus)
    if ($error === 4) {
        showSweetAlert('Gagal!', 'Pilih file terlebih dahulu.', 'warning', '../dashboard.php');
    }

    // Validasi 2: Ekstensi File yang diizinkan (Keamanan Dasar)
    $ekstensi_valid = ['jpg', 'jpeg', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'zip', 'rar'];
    $ekstensi_file = explode('.', $original_name);
    $ekstensi_file = strtolower(end($ekstensi_file));

    if (!in_array($ekstensi_file, $ekstensi_valid)) {
        catatLog($conn, $username, strtoupper($role), 'UPLOAD', $original_name, 'DITOLAK (Ekstensi)');
        showSweetAlert('Format Tidak Valid!', 'Hanya mendukung dokumen, gambar, dan arsip.', 'error', '../dashboard.php');
    }

    // Validasi 3: Batasan Ukuran File (Maksimal 5MB)
    // 5MB = 5 * 1024 * 1024 byte
    if ($file_size > 5242880) {
        catatLog($conn, $username, strtoupper($role), 'UPLOAD', $original_name, 'DITOLAK (Ukuran Besar)');
        showSweetAlert('File Terlalu Besar!', 'Ukuran maksimal file adalah 5MB.', 'warning', '../dashboard.php');
    }

    // Validasi 4: Rename File agar tidak saling menimpa
    // Format baru: timestamp_unik_namaasli.ekstensi
    $new_filename = time() . '_' . uniqid() . '.' . $ekstensi_file;
    $folder_tujuan = '../uploads/' . $new_filename;

    // 3. PINDAHKAN FILE KE SERVER & SIMPAN KE DATABASE
    if (move_uploaded_file($tmp_name, $folder_tujuan)) {
        
        // Simpan metadata ke tabel files
        $query_insert = "INSERT INTO files (user_id, filename, original_name, file_size, file_type) 
                         VALUES ('$user_id', '$new_filename', '$original_name', '$file_size', '$ekstensi_file')";
        
        if ($conn->query($query_insert)) {
            // Catat log BERHASIL
            catatLog($conn, $username, strtoupper($role), 'UPLOAD', $original_name, 'BERHASIL');
            showSweetAlert('Berhasil!', 'File Anda telah diamankan di cloud.', 'success', '../dashboard.php');
        } else {
            // Jika masuk database gagal, hapus file fisiknya
            unlink($folder_tujuan);
            showSweetAlert('Error Database', 'Gagal menyimpan data file.', 'error', '../dashboard.php');
        }

    } else {
        catatLog($conn, $username, strtoupper($role), 'UPLOAD', $original_name, 'GAGAL (Server Error)');
        showSweetAlert('Gagal Mengunggah', 'Terjadi kesalahan pada server saat memindahkan file.', 'error', '../dashboard.php');
    }
}
?>