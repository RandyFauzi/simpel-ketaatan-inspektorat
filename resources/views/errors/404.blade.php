<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>404 - Objek Pemeriksaan Tidak Ditemukan</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        /* Custom Keyframe Animations */
        @keyframes gradientFade {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .animate-gradient {
            background-size: 200% 200%;
            animation: gradientFade 12s ease infinite;
        }

        @keyframes float-slow {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
        }
        .animate-float-slow {
            animation: float-slow 4s ease-in-out infinite;
        }

        @keyframes float-delay {
            0%, 100% { transform: translateY(0) rotate(-2deg); }
            50% { transform: translateY(-15px) rotate(-6deg); }
        }
        .animate-float-delay {
            animation: float-delay 5s ease-in-out infinite;
            animation-delay: 1.5s;
        }

        /* Decorative Background Particles Floating Upwards */
        @keyframes drift {
            0% { transform: translateY(110vh) translateX(-10vw) rotate(0deg); opacity: 0; }
            10% { opacity: 0.15; }
            90% { opacity: 0.15; }
            100% { transform: translateY(-20vh) translateX(20vw) rotate(360deg); opacity: 0; }
        }
        
        .particle { position: absolute; opacity: 0; }
        .particle-1 { animation: drift 15s linear infinite; left: 10%; width: 40px; }
        .particle-2 { animation: drift 22s linear infinite; animation-delay: -5s; left: 30%; width: 50px; }
        .particle-3 { animation: drift 18s linear infinite; animation-delay: -12s; left: 60%; width: 35px; }
        .particle-4 { animation: drift 26s linear infinite; animation-delay: -2s; left: 80%; width: 60px; }
        .particle-5 { animation: drift 20s linear infinite; animation-delay: -16s; left: 90%; width: 45px; }
        .particle-6 { animation: drift 17s linear infinite; animation-delay: -8s; left: 45%; width: 30px; }

        /* Shine Effect for the Button */
        .shine-button {
            position: relative;
            overflow: hidden;
        }
        .shine-button::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(to right, rgba(255,255,255,0) 0%, rgba(255,255,255,0.4) 50%, rgba(255,255,255,0) 100%);
            transform: rotate(30deg) translateX(-150%);
            transition: transform 0.8s cubic-bezier(0.19, 1, 0.22, 1);
        }
        .shine-button:hover::after {
            transform: rotate(30deg) translateX(150%);
        }
    </style>
</head>
<body class="antialiased overflow-hidden min-h-screen bg-gradient-to-br from-slate-950 via-indigo-950 to-slate-900 animate-gradient text-slate-100 flex items-center justify-center relative font-sans">
    
    <!-- Decorative Ambient Particles (Drifting Files & Folders) -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
        <!-- SVG Document 1 -->
        <svg class="particle particle-1 text-emerald-400/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <!-- SVG Folder 2 -->
        <svg class="particle particle-2 text-indigo-400/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
        </svg>
        <!-- SVG Chart/Analytics 3 -->
        <svg class="particle particle-3 text-blue-400/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
        <!-- SVG Search/Magnify 4 -->
        <svg class="particle particle-4 text-emerald-300/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <!-- SVG Document 5 -->
        <svg class="particle particle-5 text-indigo-300/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
        </svg>
        <!-- SVG Folder 6 -->
        <svg class="particle particle-6 text-blue-300/20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z" />
        </svg>
    </div>

    <!-- Main Dynamic Content -->
    <div class="z-10 text-center px-6 max-w-3xl relative">
        <!-- The Floating 404 Visual -->
        <div class="flex justify-center items-center gap-1 md:gap-5 mb-8 select-none drop-shadow-[0_20px_20px_rgba(0,0,0,0.5)]">
            <!-- First '4' -->
            <span class="text-[130px] md:text-[220px] font-black leading-none animate-float-slow bg-clip-text text-transparent bg-gradient-to-br from-white via-slate-200 to-slate-500">4</span>
            
            <!-- The Radar '0' -->
            <div class="relative w-28 h-28 md:w-52 md:h-52 flex items-center justify-center animate-float-delay shrink-0">
                <!-- Outer Glass Ring -->
                <div class="absolute inset-0 border-[16px] md:border-[28px] border-emerald-500/70 rounded-full shadow-[0_0_50px_rgba(16,185,129,0.5)] backdrop-blur-md"></div>
                
                <!-- Inner Radar Sweep Animation -->
                <div class="absolute inset-[6px] md:inset-[12px] rounded-full border border-emerald-400/20 overflow-hidden mix-blend-screen">
                    <div class="absolute top-1/2 left-1/2 w-[100%] h-[100%] origin-top-left animate-[spin_3s_linear_infinite]" 
                         style="background: conic-gradient(from 180deg at 0 0, rgba(52,211,153,0.8), transparent 60%);">
                    </div>
                </div>

                <!-- Static Magnifying Glass Inside -->
                <svg class="w-12 h-12 md:w-20 md:h-20 text-white relative z-10 animate-pulse drop-shadow-[0_0_15px_rgba(255,255,255,0.8)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>

            <!-- Second '4' -->
            <span class="text-[130px] md:text-[220px] font-black leading-none animate-float-slow bg-clip-text text-transparent bg-gradient-to-br from-white via-slate-200 to-slate-500" style="animation-delay: 1.2s;">4</span>
        </div>

        <!-- Typography / Copywriting -->
        <div class="bg-indigo-900/30 backdrop-blur-sm border border-indigo-500/20 py-8 px-6 rounded-3xl shadow-[0_0_40px_rgba(0,0,0,0.3)]">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full bg-red-500/10 border border-red-500/30 text-red-400 text-sm font-bold mb-5 tracking-widest uppercase">
                <x-lucide-alert-triangle class="w-4 h-4" /> Error Code: 404
            </div>
            
            <h1 class="text-3xl md:text-5xl font-extrabold mb-5 text-white tracking-tight leading-tight">
                Waduh! Objek Pemeriksaan <br class="hidden md:block">Tidak Ditemukan.
            </h1>
            
            <p class="text-slate-300 md:text-lg mb-8 max-w-xl mx-auto leading-relaxed border-l-4 border-emerald-500/50 pl-5 text-left md:text-center md:border-l-0 md:border-t-4 md:pt-5 pt-0">
                Sepertinya Anda tersesat di luar ruang lingkup audit. Dokumen atau halaman target yang Anda reviu hari ini sepertinya <strong>tidak terdaftar dalam radar operasional</strong> sistem kami.
            </p>

            <!-- Interactive Action Button -->
            <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-to-r from-emerald-500 to-teal-600 border border-emerald-400 text-white font-bold rounded-full text-lg shadow-[0_10px_30px_rgba(16,185,129,0.3)] transition-all shine-button w-full sm:w-auto transform hover:-translate-y-1 group">
                <svg class="w-5 h-5 transition-transform group-hover:-translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Dashboard Utama
            </a>
        </div>
    </div>

</body>
</html>
