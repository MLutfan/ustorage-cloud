<?php
// Memulai session untuk menyimpan data login
session_start();

// Memanggil file koneksi database
require_once '../config/database.php';

// =========================================================================
// FUNGSI NOTIFIKASI SWEETALERT (TEMA DARK-NEON)
// =========================================================================
function showSweetAlert($title, $text, $icon, $redirect) {
    echo "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>$title</title>
        <link href='https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap' rel='stylesheet'>
        <script src='https://cdn.jsdelivr.net/npm/sweetalert2@11'></script>
        <style>
            body { 
                background-color: #121212; /* Warna u-dark */
                font-family: 'Poppins', sans-serif; 
                margin: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                height: 100vh;
            }
            /* Menyesuaikan border radius agar membulat seperti form kita */
            .swal2-popup { border-radius: 1rem !important; }
        </style>
    </head>
    <body>
        <script>
            Swal.fire({
                title: '$title',
                text: '$text',
                icon: '$icon',
                background: '#27272A', /* Warna u-surface */
                color: '#F8FAFC',      /* Warna teks terang */
                confirmButtonColor: '#A3E635', /* Warna hijau neon u-neon */
                confirmButtonText: '<span style=\"color:#000; font-weight:600; padding: 0 15px;\">Oke</span>',
                allowOutsideClick: false, /* Memaksa user klik tombol Oke */
                customClass: {
                    confirmButton: 'rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirect';
                }
            });
        </script>
    </body>
    </html>
    ";
    exit;
}

// =========================================================================
// 1. PROSES REGISTRASI (DARI HALAMAN register.php)
// =========================================================================
if (isset($_POST['register'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $role = $conn->real_escape_string($_POST['role']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Cek apakah password dan konfirmasi password sama
    if ($password !== $confirm_password) {
        showSweetAlert('Gagal!', 'Konfirmasi kata sandi tidak cocok!', 'error', '../register.php');
    }

    // Cek apakah username sudah dipakai di database
    $cek_user = $conn->query("SELECT id FROM users WHERE username = '$username'");
    if ($cek_user->num_rows > 0) {
        showSweetAlert('Oops!', 'Username sudah digunakan! Silakan pilih yang lain.', 'warning', '../register.php');
    }

    // Enkripsi password
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Masukkan data ke database
    if ($role === 'user' || $role === 'viewer') {
        $query = "INSERT INTO users (username, password, role) VALUES ('$username', '$hashed_password', '$role')";
        
        if ($conn->query($query) === TRUE) {
            showSweetAlert('Berhasil!', 'Akun Anda berhasil dibuat. Silakan login.', 'success', '../index.php');
        } else {
            showSweetAlert('Error Sistem', 'Terjadi kesalahan saat mendaftar.', 'error', '../register.php');
        }
    } else {
        showSweetAlert('Akses Ditolak', 'Role tidak valid!', 'error', '../register.php');
    }
}

// =========================================================================
// 2. PROSES LOGIN (DARI HALAMAN index.php)
// =========================================================================
if (isset($_POST['login'])) {
    $username = $conn->real_escape_string(trim($_POST['username']));
    $password = $_POST['password'];

    // Cari user
    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $user_data = $result->fetch_assoc();

        // Cocokkan password
        if (password_verify($password, $user_data['password'])) {
            
            // Buat Session
            $_SESSION['user_id'] = $user_data['id'];
            $_SESSION['username'] = $user_data['username'];
            $_SESSION['role'] = $user_data['role'];

            // Arahkan ke halaman sesuai level akses
            if ($_SESSION['role'] == 'admin') {
                showSweetAlert('Login Berhasil', 'Selamat datang, Admin ' . $_SESSION['username'], 'success', '../admin.php');
            } else {
                showSweetAlert('Login Berhasil', 'Selamat datang kembali, ' . $_SESSION['username'], 'success', '../dashboard.php');
            }

        } else {
            showSweetAlert('Gagal Masuk', 'Kata sandi yang Anda masukkan salah!', 'error', '../index.php');
        }
    } else {
        showSweetAlert('Gagal Masuk', 'Username tidak ditemukan!', 'error', '../index.php');
    }
}

// Jika diakses langsung tanpa POST
if (!isset($_POST['login']) && !isset($_POST['register'])) {
    header("Location: ../index.php");
    exit;
}
?>