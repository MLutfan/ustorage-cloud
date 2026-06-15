<?php
session_start();
require_once 'config/database.php';
require_once 'process/logger.php';

// Pengecekan sesi
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Ambil data user terbaru dari database
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = $conn->query($query);
$user_data = $result->fetch_assoc();
$current_username = $user_data['username'];

// Variabel untuk menampung script SweetAlert
$alert_script = "";

// ==========================================
// PROSES UPDATE PROFIL
// ==========================================
if (isset($_POST['update_profile'])) {
    $new_username = $conn->real_escape_string(trim($_POST['username']));
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $is_valid = true;

    // 1. Cek apakah username diubah dan apakah sudah dipakai orang lain
    if ($new_username !== $current_username) {
        $cek = $conn->query("SELECT id FROM users WHERE username = '$new_username' AND id != '$user_id'");
        if ($cek->num_rows > 0) {
            $alert_script = "Swal.fire({icon: 'error', title: 'Oops...', text: 'Username sudah digunakan pengguna lain!', background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#A3E635', customClass: {confirmButton: 'rounded-xl text-black font-bold'}});";
            $is_valid = false;
        }
    }

    if ($is_valid) {
        // 2. Jika input password lama diisi (artinya mau ganti password)
        if (!empty($old_password) || !empty($new_password)) {
            
            // Cocokkan password lama
            if (password_verify($old_password, $user_data['password'])) {
                
                // Pastikan password baru dan konfirmasi sama
                if ($new_password === $confirm_password && strlen($new_password) >= 8) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    // Update username & password
                    $conn->query("UPDATE users SET username = '$new_username', password = '$hashed_password' WHERE id = '$user_id'");
                    catatLog($conn, $new_username, strtoupper($role), 'UPDATE', 'Mengubah profil dan kata sandi', 'BERHASIL');
                    
                    // Perbarui session
                    $_SESSION['username'] = $new_username;
                    $current_username = $new_username;
                    
                    $alert_script = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Profil dan sandi berhasil diperbarui.', background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#A3E635', customClass: {confirmButton: 'rounded-xl text-black font-bold'}});";
                } else {
                    $alert_script = "Swal.fire({icon: 'error', title: 'Gagal', text: 'Konfirmasi sandi tidak cocok atau kurang dari 8 karakter!', background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#A3E635', customClass: {confirmButton: 'rounded-xl text-black font-bold'}});";
                }
            } else {
                $alert_script = "Swal.fire({icon: 'error', title: 'Akses Ditolak', text: 'Kata sandi lama yang Anda masukkan salah!', background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#A3E635', customClass: {confirmButton: 'rounded-xl text-black font-bold'}});";
            }
            
        } else {
            // 3. Hanya update username (password tidak diubah)
            $conn->query("UPDATE users SET username = '$new_username' WHERE id = '$user_id'");
            catatLog($conn, $new_username, strtoupper($role), 'UPDATE', 'Mengubah username', 'BERHASIL');
            
            $_SESSION['username'] = $new_username;
            $current_username = $new_username;
            
            $alert_script = "Swal.fire({icon: 'success', title: 'Berhasil!', text: 'Username berhasil diperbarui.', background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#A3E635', customClass: {confirmButton: 'rounded-xl text-black font-bold'}});";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - ustorage</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">

    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { spartan: ['"League Spartan"', 'sans-serif'], poppins: ['Poppins', 'sans-serif'], }, colors: { 'u-dark': '#121212', 'u-surface': '#27272A', 'u-neon': '#A3E635', 'u-text-muted': '#9CA3AF', 'u-text-light': '#F8FAFC', } } } }
    </script>
    <style>body { font-family: 'Poppins', sans-serif; background-color: #121212; color: #F8FAFC; }</style>
</head>
<body class="h-screen w-full flex overflow-hidden">

    <aside class="w-20 md:w-64 bg-u-surface border-r border-u-dark flex flex-col justify-between py-6 transition-all duration-300">
        <div>
            <div class="px-6 mb-10 flex items-center justify-center md:justify-start">
                <h1 class="text-2xl font-bold font-spartan hidden md:block">ustorage<span class="text-u-neon">.</span></h1>
                <div class="w-10 h-10 bg-u-neon rounded-xl flex items-center justify-center md:hidden">
                    <span class="text-black font-spartan font-bold text-xl">u</span>
                </div>
            </div>
            
            <nav class="space-y-2 px-3">
                <a href="<?= ($role === 'admin') ? 'admin.php' : 'dashboard.php' ?>" class="flex items-center gap-4 px-3 py-3 rounded-xl text-u-text-muted hover:bg-u-dark/30 hover:text-u-text-light transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    <span class="hidden md:block font-medium">Beranda</span>
                </a>
                <a href="profile.php" class="flex items-center gap-4 px-3 py-3 rounded-xl bg-u-dark/50 text-u-neon border-l-2 border-u-neon transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    <span class="hidden md:block font-medium">Profil</span>
                </a>
            </nav>
        </div>

        <div class="px-3">
            <button onclick="confirmLogout()" class="w-full flex items-center gap-4 px-3 py-3 rounded-xl text-u-text-muted hover:bg-red-500/10 hover:text-red-500 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                <span class="hidden md:block font-medium">Keluar</span>
            </button>
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto bg-u-dark p-6 md:p-10 relative">
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-u-neon/5 rounded-full blur-[120px] pointer-events-none"></div>

        <header class="mb-10">
            <h2 class="text-3xl font-spartan font-bold">Pengaturan Profil</h2>
            <p class="text-u-text-muted text-sm mt-1">Kelola informasi akun dan keamanan Anda.</p>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            
            <div class="xl:col-span-1">
                <div class="bg-u-surface p-8 rounded-3xl border border-u-dark shadow-lg flex flex-col items-center text-center">
                    <div class="w-24 h-24 rounded-full bg-u-neon text-black flex items-center justify-center font-spartan font-bold text-4xl mb-4 shadow-[0_0_20px_rgba(163,230,53,0.3)]">
                        <?= strtoupper(substr($current_username, 0, 1)) ?>
                    </div>
                    <h3 class="text-xl font-spartan font-bold text-u-text-light"><?= htmlspecialchars($current_username) ?></h3>
                    <span class="mt-2 text-xs text-u-neon bg-u-neon/10 px-3 py-1 rounded-full uppercase tracking-wider font-semibold">
                        Role: <?= htmlspecialchars($role) ?>
                    </span>
                    <p class="mt-6 text-sm text-u-text-muted">Bergabung sejak: <br><span class="text-u-text-light"><?= date('d F Y', strtotime($user_data['created_at'])) ?></span></p>
                </div>
            </div>

            <div class="xl:col-span-2 bg-u-surface p-8 rounded-3xl border border-u-dark shadow-lg">
                <form action="profile.php" method="POST" class="space-y-6">
                    
                    <div>
                        <label for="username" class="block text-sm text-u-text-muted mb-2">Username</label>
                        <input type="text" id="username" name="username" value="<?= htmlspecialchars($current_username) ?>" required
                            class="w-full px-4 py-3 rounded-xl bg-u-dark text-u-text-light border border-u-surface focus:border-u-neon focus:outline-none transition-colors">
                    </div>

                    <hr class="border-u-dark">

                    <div>
                        <h4 class="text-lg font-spartan font-bold text-u-text-light mb-4">Ubah Kata Sandi <span class="text-xs font-normal text-u-text-muted ml-2">(Kosongkan jika tidak ingin mengubah)</span></h4>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="old_password" class="block text-sm text-u-text-muted mb-2">Kata Sandi Saat Ini</label>
                                <input type="password" id="old_password" name="old_password" placeholder="Masukkan kata sandi lama untuk validasi"
                                    class="w-full px-4 py-3 rounded-xl bg-u-dark text-u-text-light border border-u-surface focus:border-u-neon focus:outline-none transition-colors">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="new_password" class="block text-sm text-u-text-muted mb-2">Kata Sandi Baru</label>
                                    <input type="password" id="new_password" name="new_password" placeholder="Minimal 8 karakter"
                                        class="w-full px-4 py-3 rounded-xl bg-u-dark text-u-text-light border border-u-surface focus:border-u-neon focus:outline-none transition-colors">
                                </div>
                                <div>
                                    <label for="confirm_password" class="block text-sm text-u-text-muted mb-2">Konfirmasi Sandi Baru</label>
                                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi sandi baru"
                                        class="w-full px-4 py-3 rounded-xl bg-u-dark text-u-text-light border border-u-surface focus:border-u-neon focus:outline-none transition-colors">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-end">
                        <button type="submit" name="update_profile"
                            class="px-8 py-3 rounded-xl bg-u-neon text-black font-semibold hover:bg-opacity-80 transition-all shadow-[0_0_15px_rgba(163,230,53,0.15)]">
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>

        </div>

        <footer class="mt-12 pt-6 border-t border-u-dark/80 text-center">
            <p class="text-xs text-u-text-muted">
                &copy; <?= date('Y') ?> ustorage. Dibuat dengan <span class="text-u-neon">&hearts;</span> oleh <span class="font-bold text-u-text-light">Muhammad Lutfan</span>
            </p>
        </footer>
    </main>

    <script>
        // Eksekusi alert jika ada pesan dari PHP
        <?= $alert_script ?>

        function confirmLogout() {
            Swal.fire({
                title: 'Keluar?', icon: 'warning', showCancelButton: true,
                background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#EF4444', cancelButtonColor: '#4B5563',
                confirmButtonText: 'Ya', cancelButtonText: 'Batal', customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'process/logout.php'; } })
        }
    </script>
</body>
</html>