<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - ustorage</title>
    <link rel="icon" type="image/svg+xml" href="assets/img/logo.svg">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=League+Spartan:wght@600;700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script src="https://unpkg.com/@dotlottie/player-component@latest/dist/dotlottie-player.mjs" type="module"></script>
    
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        spartan: ['"League Spartan"', 'sans-serif'],
                        poppins: ['Poppins', 'sans-serif'],
                    },
                    colors: {
                        'u-dark': '#121212',
                        'u-surface': '#27272A',
                        'u-neon': '#A3E635',
                        'u-text-muted': '#9CA3AF',
                        'u-text-light': '#F8FAFC',
                    }
                }
            }
        }
    </script>

    <style>
        body { 
            font-family: 'Poppins', sans-serif; 
            background-color: #121212; 
            color: #F8FAFC; 
        }
        
        /* Kustomisasi scrollbar untuk dropdown jika kepanjangan */
        select option {
            background-color: #27272A;
            color: #F8FAFC;
        }
    </style>
</head>
<body class="h-screen w-full flex overflow-hidden">

    <div class="w-full md:w-1/2 flex flex-col justify-center px-8 sm:px-16 lg:px-24 bg-u-dark z-10 overflow-y-auto py-8">
        
        <div class="mb-8">
            <h1 class="text-4xl font-bold font-spartan tracking-wide">ustorage<span class="text-u-neon">.</span></h1>
        </div>

        <div class="mb-8">
            <h2 class="text-3xl font-bold font-spartan text-u-text-light mb-2">Buat Akun Baru</h2>
            <p class="text-u-text-muted text-sm">Bergabunglah untuk mulai menyimpan dan mengelola file Anda.</p>
        </div>

        <form action="process/auth_process.php" method="POST" class="space-y-4">
            
            <div>
                <label for="username" class="block text-sm text-u-text-muted mb-1.5">Username</label>
                <input type="text" id="username" name="username" placeholder="Pilih username" required
                    class="w-full px-4 py-3 rounded-xl bg-u-surface text-u-text-light border border-transparent focus:border-u-neon focus:outline-none transition-colors duration-300">
            </div>

            <div>
                <label for="role" class="block text-sm text-u-text-muted mb-1.5">Tipe Akun (Level Akses)</label>
                <select id="role" name="role" required
                    class="w-full px-4 py-3 rounded-xl bg-u-surface text-u-text-light border border-transparent focus:border-u-neon focus:outline-none transition-colors duration-300 appearance-none cursor-pointer">
                    <option value="user">User (Dapat kelola file sendiri)</option>
                    <option value="viewer">Viewer (Hanya akses terbatas)</option>
                </select>
            </div>

            <div>
                <label for="password" class="block text-sm text-u-text-muted mb-1.5">Kata Sandi</label>
                <div class="relative flex items-center">
                    <input type="password" id="password" name="password" placeholder="Buat kata sandi minimal 8 karakter" required minlength="8"
                        class="w-full px-4 py-3 pr-12 rounded-xl bg-u-surface text-u-text-light border border-transparent focus:border-u-neon focus:outline-none transition-colors duration-300">
                    <button type="button" onclick="togglePassword('password', this)" class="absolute right-4 text-u-text-muted hover:text-u-neon focus:outline-none transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 eye-closed"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 eye-open hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>
            </div>

            <div>
                <label for="confirm_password" class="block text-sm text-u-text-muted mb-1.5">Konfirmasi Kata Sandi</label>
                <div class="relative flex items-center">
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi Anda" required minlength="8"
                        class="w-full px-4 py-3 pr-12 rounded-xl bg-u-surface text-u-text-light border border-transparent focus:border-u-neon focus:outline-none transition-colors duration-300">
                    <button type="button" onclick="togglePassword('confirm_password', this)" class="absolute right-4 text-u-text-muted hover:text-u-neon focus:outline-none transition-colors duration-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 eye-closed"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6 eye-open hidden"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                    </button>
                </div>
            </div>

            <button type="submit" name="register"
                class="w-full py-3.5 mt-2 rounded-xl bg-u-neon text-black font-semibold text-lg hover:bg-opacity-80 transition-all duration-300 shadow-[0_0_15px_rgba(163,230,53,0.3)] hover:shadow-[0_0_25px_rgba(163,230,53,0.5)]">
                Daftar Sekarang
            </button>
        </form>

        <div class="mt-6 text-center md:text-left">
            <p class="text-u-text-muted text-sm">Sudah punya akun? 
                <a href="index.php" class="text-u-text-light font-medium hover:text-u-neon transition-colors duration-300">Masuk di sini</a>
            </p>
        </div>
    </div>

    <div class="hidden md:flex md:w-1/2 relative bg-u-surface items-center justify-center overflow-hidden">
        
        <div class="absolute w-[500px] h-[500px] bg-u-neon/10 rounded-full blur-[100px]"></div>

        <dotlottie-player 
            src="https://lottie.host/2f40a8b9-7021-48a9-9912-89e13622d1c8/0qh8MsTabZ.lottie" 
            background="transparent" 
            speed="1" 
            style="width: 80%; max-width: 600px; height: auto;" 
            loop 
            autoplay>
        </dotlottie-player>
    </div>

    <script>
        function togglePassword(inputId, buttonElement) {
            const input = document.getElementById(inputId);
            const eyeClosed = buttonElement.querySelector('.eye-closed');
            const eyeOpen = buttonElement.querySelector('.eye-open');

            if (input.type === 'password') {
                input.type = 'text';
                eyeClosed.classList.add('hidden');
                eyeOpen.classList.remove('hidden');
                buttonElement.classList.add('text-u-neon'); // Memberi efek hijau saat dilihat
            } else {
                input.type = 'password';
                eyeClosed.classList.remove('hidden');
                eyeOpen.classList.add('hidden');
                buttonElement.classList.remove('text-u-neon');
            }
        }
    </script>

</body>
</html>