<?php
session_start();
require_once 'config/database.php';

// PENGECEKAN KEAMANAN EKSTRA KETAT
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: dashboard.php");
    exit;
}

$username = $_SESSION['username'];

// Mengambil Statistik Singkat
$stat_users = $conn->query("SELECT COUNT(id) AS total FROM users")->fetch_assoc()['total'];
$stat_files_active = $conn->query("SELECT COUNT(id) AS total FROM files WHERE status = 'active'")->fetch_assoc()['total'];
$stat_files_trash = $conn->query("SELECT COUNT(id) AS total FROM files WHERE status = 'trash'")->fetch_assoc()['total'];

// Mengambil Data Audit Log
$result_logs = $conn->query("SELECT * FROM logs ORDER BY created_at DESC LIMIT 50");

// Mengambil Data Seluruh File (Join dengan Users untuk tahu pemiliknya)
$result_files = $conn->query("SELECT files.*, users.username AS pemilik FROM files JOIN users ON files.user_id = users.id ORDER BY uploaded_at DESC");

// Mengambil Data Pengguna
$result_users = $conn->query("SELECT id, username, role, created_at FROM users ORDER BY role ASC, created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - ustorage</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = { theme: { extend: { fontFamily: { spartan: ['"League Spartan"', 'sans-serif'], poppins: ['Poppins', 'sans-serif'], }, colors: { 'u-dark': '#121212', 'u-surface': '#27272A', 'u-neon': '#A3E635', 'u-text-muted': '#9CA3AF', 'u-text-light': '#F8FAFC', } } } }
    </script>
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #121212; color: #F8FAFC; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #4B5563; border-radius: 10px; }
    </style>
</head>
<body class="h-screen w-full flex overflow-hidden">

    <aside class="w-20 md:w-64 bg-u-surface border-r border-u-dark flex flex-col justify-between py-6 transition-all duration-300 z-20">
        <div>
            <div class="px-6 mb-10 flex flex-col items-center justify-center md:items-start">
                <h1 class="text-2xl font-bold font-spartan hidden md:block">ustorage<span class="text-u-neon">.</span></h1>
                <div class="w-10 h-10 bg-u-neon rounded-xl flex items-center justify-center md:hidden mb-2">
                    <span class="text-black font-spartan font-bold text-xl">u</span>
                </div>
                <span class="bg-red-500/20 text-red-500 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider hidden md:inline-block mt-1">Admin Panel</span>
            </div>
            
            <nav class="space-y-2 px-3">
                <a href="admin.php" class="flex items-center gap-4 px-3 py-3 rounded-xl bg-u-dark/50 text-u-neon border-l-2 border-u-neon transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 11-3 0m3 0a1.5 1.5 0 10-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 01-3 0m3 0a1.5 1.5 0 00-3 0m-9.75 0h9.75" /></svg>
                    <span class="hidden md:block font-medium">Pusat Kendali</span>
                </a>
                <a href="profile.php" class="flex items-center gap-4 px-3 py-3 rounded-xl text-u-text-muted hover:bg-u-dark/30 hover:text-u-text-light transition-colors">
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
        <div class="absolute top-0 right-1/4 w-96 h-96 bg-red-500/5 rounded-full blur-[120px] pointer-events-none"></div>

        <header class="mb-10">
            <h2 class="text-3xl font-spartan font-bold">Pusat Kendali Admin</h2>
            <p class="text-u-text-muted text-sm mt-1">Kelola seluruh aktivitas, pengguna, dan berkas di dalam sistem.</p>
        </header>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-u-surface border border-u-dark p-6 rounded-3xl shadow-lg">
                <p class="text-u-text-muted text-sm mb-1">Total Pengguna</p>
                <p class="text-3xl font-spartan font-bold text-u-text-light"><?= $stat_users ?></p>
            </div>
            <div class="bg-u-surface border border-u-dark p-6 rounded-3xl shadow-lg">
                <p class="text-u-text-muted text-sm mb-1">File Aktif (Uploads)</p>
                <p class="text-3xl font-spartan font-bold text-u-neon"><?= $stat_files_active ?></p>
            </div>
            <div class="bg-u-surface border border-u-dark p-6 rounded-3xl shadow-lg">
                <p class="text-u-text-muted text-sm mb-1">File Terhapus (Trash)</p>
                <p class="text-3xl font-spartan font-bold text-red-400"><?= $stat_files_trash ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-8">
            
            <div class="space-y-8">
                
                <div class="bg-u-surface p-6 rounded-3xl border border-u-dark shadow-lg">
                    <h3 class="text-lg font-spartan font-bold mb-4">Manajemen Seluruh File</h3>
                    <div class="overflow-y-auto max-h-[300px] custom-scrollbar pr-2">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="sticky top-0 bg-u-surface z-10">
                                <tr class="text-u-text-muted border-b border-u-dark">
                                    <th class="pb-3 font-medium">Pemilik & File</th>
                                    <th class="pb-3 font-medium text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-u-dark/50">
                                <?php if ($result_files->num_rows > 0): ?>
                                    <?php while($f = $result_files->fetch_assoc()): ?>
                                    <tr class="hover:bg-u-dark/30 transition-colors">
                                        <td class="py-3">
                                            <p class="font-medium text-u-text-light truncate max-w-[200px]"><?= htmlspecialchars($f['original_name']) ?></p>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-[10px] text-u-text-muted">@<?= htmlspecialchars($f['pemilik']) ?></span>
                                                <?php if ($f['status'] === 'active'): ?>
                                                    <span class="text-[10px] bg-emerald-500/20 text-emerald-400 px-1.5 rounded">Active</span>
                                                <?php else: ?>
                                                    <span class="text-[10px] bg-orange-500/20 text-orange-400 px-1.5 rounded">Trash</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="py-3 text-right">
                                            <?php if ($f['status'] === 'trash'): ?>
                                                <div class="flex justify-end gap-1">
                                                    <button onclick="confirmRestore(<?= $f['id'] ?>)" class="p-1.5 bg-u-dark rounded-md text-emerald-400 hover:text-emerald-300" title="Kembalikan File">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M9 15L3 9m0 0l6-6M3 9h12a6 6 0 010 12h-3" /></svg>
                                                    </button>
                                                    <button onclick="confirmHardDelete(<?= $f['id'] ?>)" class="p-1.5 bg-u-dark rounded-md text-red-500 hover:text-red-400" title="Hapus Permanen">
                                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" /></svg>
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <a href="process/download_process.php?id=<?= $f['id'] ?>" class="inline-block p-1.5 bg-u-dark rounded-md text-blue-400 hover:text-blue-300" title="Unduh">
                                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr><td colspan="2" class="py-4 text-center text-u-text-muted">Tidak ada file.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-u-surface p-6 rounded-3xl border border-u-dark shadow-lg">
                    <h3 class="text-lg font-spartan font-bold mb-4">Daftar Pengguna</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm border-collapse">
                            <thead class="bg-u-surface">
                                <tr class="text-u-text-muted border-b border-u-dark">
                                    <th class="pb-3 font-medium">Username</th>
                                    <th class="pb-3 font-medium">Role</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-u-dark/50">
                                <?php while($u = $result_users->fetch_assoc()): ?>
                                <tr>
                                    <td class="py-3 text-u-text-light font-medium"><?= htmlspecialchars($u['username']) ?></td>
                                    <td class="py-3">
                                        <span class="text-[10px] bg-u-dark px-2 py-1 rounded-md uppercase tracking-wider text-u-text-muted">
                                            <?= htmlspecialchars($u['role']) ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="bg-u-surface p-6 rounded-3xl border border-u-dark shadow-lg h-fit">
                <h3 class="text-lg font-spartan font-bold mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-u-neon"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" /></svg>
                    Audit Log Sistem (50 Terakhir)
                </h3>
                <div class="overflow-y-auto max-h-[600px] custom-scrollbar pr-2">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-u-surface z-10">
                            <tr class="text-u-text-muted border-b border-u-dark">
                                <th class="pb-3 font-medium">User</th>
                                <th class="pb-3 font-medium">Aktivitas</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-u-dark/50">
                            <?php if ($result_logs->num_rows > 0): ?>
                                <?php while($log = $result_logs->fetch_assoc()): ?>
                                <tr class="hover:bg-u-dark/30 transition-colors">
                                    <td class="py-3">
                                        <p class="font-medium text-u-text-light"><?= htmlspecialchars($log['username']) ?></p>
                                        <p class="text-[10px] text-u-text-muted"><?= date('d/m/y H:i', strtotime($log['created_at'])) ?></p>
                                    </td>
                                    <td class="py-3">
                                        <p class="text-xs text-u-text-muted max-w-[200px] truncate" title="<?= htmlspecialchars($log['detail']) ?>">
                                            <span class="font-semibold text-u-text-light"><?= htmlspecialchars($log['action']) ?></span>: <?= htmlspecialchars($log['detail']) ?>
                                        </p>
                                        <?php if (strpos($log['status'], 'BERHASIL') !== false): ?>
                                            <span class="text-[10px] font-semibold text-emerald-400 mt-1 block"><?= htmlspecialchars($log['status']) ?></span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-semibold text-red-400 mt-1 block"><?= htmlspecialchars($log['status']) ?></span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="py-6 text-center text-u-text-muted">Belum ada log.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <footer class="mt-12 pt-6 border-t border-u-dark/80 text-center">
            <p class="text-xs text-u-text-muted">
                &copy; <?= date('Y') ?> ustorage. Dibuat dengan <span class="text-u-neon">&hearts;</span> oleh <span class="font-bold text-u-text-light">Muhammad Lutfan</span>
            </p>
        </footer>

    </main>

    <script>
        function confirmLogout() {
            Swal.fire({
                title: 'Tutup Sesi Admin?', icon: 'warning', showCancelButton: true,
                background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#EF4444', cancelButtonColor: '#4B5563',
                confirmButtonText: 'Ya', cancelButtonText: 'Batal', customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'process/logout.php'; } })
        }

        function confirmRestore(fileId) {
            Swal.fire({
                title: 'Kembalikan File?', text: "File akan dipindahkan kembali ke folder Uploads.", icon: 'question', showCancelButton: true,
                background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#34D399', cancelButtonColor: '#4B5563',
                confirmButtonText: 'Kembalikan', cancelButtonText: 'Batal', customClass: { confirmButton: 'rounded-xl text-black font-bold', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'process/restore_process.php?id=' + fileId; } })
        }

        function confirmHardDelete(fileId) {
            Swal.fire({
                title: 'Hapus Permanen?', text: "Tindakan ini tidak bisa dibatalkan. File akan musnah dari server.", icon: 'error', showCancelButton: true,
                background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#EF4444', cancelButtonColor: '#4B5563',
                confirmButtonText: 'Hapus Permanen', cancelButtonText: 'Batal', customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'process/hard_delete_process.php?id=' + fileId; } })
        }
    </script>
</body>
</html>