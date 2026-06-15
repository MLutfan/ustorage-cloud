<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['role'] === 'admin') {
    header("Location: admin.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

// Mengambil file user
$query_files = "SELECT * FROM files WHERE user_id = '$user_id' AND status = 'active' ORDER BY uploaded_at DESC";
$result_files = $conn->query($query_files);

// Menghitung total ukuran storage yang terpakai (Opsional untuk UI)
// Menghitung total ukuran storage yang terpakai
$total_size = 0;
$query_size = "SELECT SUM(file_size) as total FROM files WHERE user_id = '$user_id' AND status = 'active'";
$size_result = $conn->query($query_size);
if ($size_row = $size_result->fetch_assoc()) {
    $total_size = $size_row['total'] ? round($size_row['total'] / (1024 * 1024), 2) : 0; // dalam MB
}

// === TAMBAHAN LOGIKA PROGRESS BAR ===
// Tentukan batas maksimal penyimpanan (Misal: 50 GB = 51200 MB)
$max_storage_mb = 51200; 

// Hitung persentase (Mencegah error pembagian nol dan membatasi maksimal 100%)
$percentage = ($total_size > 0) ? ($total_size / $max_storage_mb) * 100 : 0;
if ($percentage > 100) { $percentage = 100; }
if ($percentage < 1 && $total_size > 0) { $percentage = 1; } // Beri minimal 1% jika ada file agar bar sedikit terlihat

// Hitung sisa kapasitas dalam GB
$sisa_storage_gb = round(($max_storage_mb - $total_size) / 1024, 2);
// ===================================
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - ustorage</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { spartan: ['"League Spartan"', 'sans-serif'], poppins: ['Poppins', 'sans-serif'], },
                    colors: { 'u-dark': '#121212', 'u-surface': '#27272A', 'u-neon': '#A3E635', 'u-text-muted': '#9CA3AF', 'u-text-light': '#F8FAFC', }
                }
            }
        }
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
                <a href="dashboard.php" class="flex items-center gap-4 px-3 py-3 rounded-xl bg-u-dark/50 text-u-neon border-l-2 border-u-neon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    <span class="hidden md:block font-medium">Beranda</span>
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
        <div class="absolute top-0 left-1/4 w-96 h-96 bg-u-neon/5 rounded-full blur-[120px] pointer-events-none"></div>

        <header class="flex justify-between items-center mb-10">
            <div>
                <p class="text-u-text-muted mb-1 text-sm">Selamat Datang,</p>
                <h2 class="text-3xl font-spartan font-bold"><?= htmlspecialchars($username) ?></h2>
            </div>
            <div class="flex items-center gap-3 bg-u-surface px-4 py-2 rounded-full border border-u-dark shadow-sm">
                <span class="text-xs text-u-neon uppercase tracking-wider font-semibold hidden md:block"><?= htmlspecialchars($role) ?></span>
                <div class="w-8 h-8 rounded-full bg-u-neon text-black flex items-center justify-center font-bold">
                    <?= strtoupper(substr($username, 0, 1)) ?>
                </div>
            </div>
        </header>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
            
            <div class="xl:col-span-1 space-y-6">
                
                <div class="bg-u-surface p-6 rounded-3xl border border-u-dark shadow-lg">
                    <h3 class="text-sm text-u-text-muted mb-4">Total Penyimpanan Anda</h3>
                    <div class="flex items-end gap-2 mb-6">
                        <span class="text-4xl font-spartan font-bold text-u-text-light"><?= $total_size ?></span>
                        <span class="text-u-text-muted pb-1">MB</span>
                    </div>
                    
                    <div class="w-full bg-u-dark rounded-full h-2.5 mb-2 overflow-hidden">
                        <div class="bg-gradient-to-r from-emerald-400 to-u-neon h-2.5 rounded-full transition-all duration-1000 ease-out" 
                             style="width: <?= $percentage ?>%">
                        </div>
                    </div>
                    
                    <p class="text-xs text-u-text-muted text-right">Tersisa <?= $sisa_storage_gb ?> GB</p>
                </div>

                <?php if ($role === 'user'): ?>
                <div class="bg-u-surface p-6 rounded-3xl border border-dashed border-u-text-muted hover:border-u-neon transition-colors">
                    <h3 class="text-sm font-semibold mb-4">Unggah File Baru</h3>
                    <form action="process/upload_process.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                        <input type="file" name="file_upload" required class="block w-full text-xs text-u-text-muted file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-u-dark file:text-u-neon hover:file:bg-opacity-80 cursor-pointer">
                        <button type="submit" name="btn_upload" class="w-full py-2.5 rounded-xl bg-u-neon text-black text-sm font-semibold hover:bg-opacity-80 shadow-[0_0_15px_rgba(163,230,53,0.15)]">Unggah Sekarang</button>
                    </form>
                </div>
                <?php else: ?>
                <div class="bg-u-surface p-6 rounded-3xl border border-u-dark">
                    <p class="text-sm text-yellow-500 font-medium">Akses Terbatas: Viewer tidak dapat mengunggah file.</p>
                </div>
                <?php endif; ?>
            </div>

            <div class="xl:col-span-2 bg-u-surface p-6 rounded-3xl border border-u-dark shadow-lg">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-lg font-spartan font-bold">Akses File Anda</h3>
                    <span class="text-xs bg-u-dark text-u-text-muted px-3 py-1 rounded-full"><?= $result_files->num_rows ?> File</span>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm border-collapse">
                        <tbody class="divide-y divide-u-dark/50">
                            <?php if ($result_files->num_rows > 0): ?>
                                <?php while($row = $result_files->fetch_assoc()): ?>
                                <tr class="hover:bg-u-dark/20 transition-colors group">
                                    <td class="py-4 flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl bg-u-dark flex items-center justify-center text-u-neon">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" /></svg>
                                        </div>
                                        <div>
                                            <p class="font-medium text-u-text-light truncate max-w-[150px] md:max-w-[250px]"><?= htmlspecialchars($row['original_name']) ?></p>
                                            <p class="text-xs text-u-text-muted"><?= round($row['file_size'] / 1024, 2) ?> KB</p>
                                        </div>
                                    </td>
                                    <td class="py-4 text-right">
                                        <div class="flex justify-end gap-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity">
                                            <a href="process/view_process.php?id=<?= $row['id'] ?>" target="_blank" class="px-3 py-1.5 rounded-lg bg-u-dark text-emerald-400 hover:text-emerald-300 text-xs font-medium">Lihat</a>
                                            <a href="process/download_process.php?id=<?= $row['id'] ?>" class="px-3 py-1.5 rounded-lg bg-u-dark text-blue-400 hover:text-blue-300 text-xs font-medium">Unduh</a>
                                            <?php if ($role === 'user'): ?>
                                            <button onclick="confirmDelete(<?= $row['id'] ?>)" class="px-3 py-1.5 rounded-lg bg-u-dark text-red-500 hover:text-red-400 text-xs font-medium">Hapus</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="2" class="py-8 text-center text-u-text-muted">Penyimpanan masih kosong.</td></tr>
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
                title: 'Keluar?', icon: 'warning', showCancelButton: true,
                background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#EF4444', cancelButtonColor: '#4B5563',
                confirmButtonText: 'Ya', cancelButtonText: 'Batal', customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'process/logout.php'; } })
        }
        function confirmDelete(fileId) {
            Swal.fire({
                title: 'Hapus File?', text: "File akan dipindahkan ke Trash.", icon: 'warning', showCancelButton: true,
                background: '#27272A', color: '#F8FAFC', confirmButtonColor: '#EF4444', cancelButtonColor: '#4B5563',
                confirmButtonText: 'Hapus', cancelButtonText: 'Batal', customClass: { confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            }).then((result) => { if (result.isConfirmed) { window.location.href = 'process/delete_process.php?id=' + fileId; } })
        }
    </script>
</body>
</html>