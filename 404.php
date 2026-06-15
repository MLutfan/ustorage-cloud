<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Halaman Tidak Ditemukan</title>
    
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    
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
<body class="h-screen w-full flex flex-col items-center justify-center p-6 text-center relative overflow-hidden">
    
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-u-neon/10 rounded-full blur-[120px] pointer-events-none"></div>

    <div class="w-full max-w-sm md:max-w-md mb-4 z-10">
        <dotlottie-player 
            src="https://lottie.host/a64c0eac-e43d-4218-b558-08720b5fdcb6/Vp4hrCEE56.lottie" 
            background="transparent" 
            speed="1" 
            style="width: 100%; height: auto;" 
            loop 
            autoplay>
        </dotlottie-player>
    </div>

    <div class="z-10 mt-[-20px]">
        <h1 class="text-4xl md:text-5xl font-spartan font-bold mb-4 text-u-text-light tracking-wide">
            Oops! <span class="text-u-neon">404</span>
        </h1>
        <p class="text-u-text-muted mb-8 max-w-md mx-auto text-sm md:text-base">
            Halaman atau file yang Anda cari mungkin telah dihapus, dipindahkan, atau Anda salah memasukkan alamat URL.
        </p>
        
        <a href="index.php" class="inline-flex items-center gap-2 px-8 py-3.5 rounded-xl bg-u-neon text-black font-semibold hover:bg-opacity-80 transition-all shadow-[0_0_15px_rgba(163,230,53,0.2)] hover:shadow-[0_0_25px_rgba(163,230,53,0.4)]">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Kembali ke Beranda
        </a>
    </div>
</body>
</html>