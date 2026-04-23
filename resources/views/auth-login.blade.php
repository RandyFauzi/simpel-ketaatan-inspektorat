<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - SIMPEL-KETAATAN | Sistem Penyusunan dan Pelaporan LHP Audit Ketaatan</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { margin: 0; }

        .animate-fade-in { animation: fadeIn 0.7s cubic-bezier(0.16,1,0.3,1) forwards; }
        .animate-fade-in-up { animation: fadeInUp 0.8s cubic-bezier(0.16,1,0.3,1) forwards; }
        @keyframes fadeIn { from{opacity:0} to{opacity:1} }
        @keyframes fadeInUp { from{opacity:0;transform:translateY(30px)} to{opacity:1;transform:translateY(0)} }
        .delay-100 { animation-delay:.1s; opacity:0; }
        .delay-200 { animation-delay:.2s; opacity:0; }
        .delay-300 { animation-delay:.3s; opacity:0; }
    </style>
</head>
<body class="min-h-screen bg-gradient-to-br from-white via-slate-50 to-blue-50/40 font-sans flex items-center justify-center p-4 sm:p-6 relative overflow-hidden">

    <div class="absolute inset-0 pointer-events-none">
        <div class="absolute top-0 right-0 w-[50%] h-[50%] rounded-full bg-blue-50"></div>
        <div class="absolute bottom-0 left-0 w-[40%] h-[40%] rounded-full bg-emerald-50"></div>
    </div>

    <div class="w-full max-w-[460px] relative z-10">

        <!-- Branding -->
        <div class="text-center mb-8 animate-fade-in-up">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-white rounded-2xl shadow-sm border border-slate-200 mb-5 p-2.5">
                <img src="{{ asset('logo.png') }}" alt="Logo" class="w-full h-full object-contain">
            </div>
            <h1 class="text-2xl sm:text-[26px] font-extrabold text-slate-800 tracking-tight leading-tight">
                SIMPEL-KETAATAN
            </h1>
            <p class="text-[11px] text-slate-400 font-semibold mt-1.5 tracking-wide uppercase">
                Sistem Penyusunan dan Pelaporan LHP Audit Ketaatan
            </p>
        </div>

        <!-- Login Card (Glassmorphism) -->
        <div class="bg-white rounded-3xl shadow-xl border border-slate-200 p-7 sm:p-9 animate-fade-in-up delay-100">

            <div class="mb-7">
                <h2 class="text-xl font-extrabold text-slate-800">Selamat Datang</h2>
                <p class="text-sm text-slate-400 font-medium mt-1">Masukkan kredensial akun Anda untuk melanjutkan.</p>
            </div>

            <!-- Error Banner -->
            @if($errors->any())
            <div class="mb-6 bg-red-50 border border-red-100 p-3.5 rounded-xl flex items-start gap-3 animate-fade-in">
                <div class="p-1 bg-red-100 rounded-lg shrink-0 mt-0.5">
                    <x-lucide-alert-triangle class="w-4 h-4 text-red-500" />
                </div>
                <div>
                    <p class="text-xs font-bold text-red-700">Akses Ditolak</p>
                    <p class="text-[11px] text-red-500 mt-0.5">{{ $errors->first() }}</p>
                </div>
            </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <!-- Email -->
                <div>
                    <label for="email" class="block text-xs font-bold text-slate-600 mb-2">Alamat Email</label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-300 group-focus-within:text-blue-500 transition-colors">
                            <x-lucide-mail class="w-[18px] h-[18px]" />
                        </div>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                            class="block w-full pl-11 pr-4 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder-slate-400 font-semibold text-slate-700"
                            placeholder="contoh@baritoselatan.go.id">
                    </div>
                </div>

                <!-- Password -->
                <div x-data="{ show: false }">
                    <div class="flex items-center justify-between mb-2">
                        <label for="password" class="block text-xs font-bold text-slate-600">Kata Sandi</label>
                        <a href="{{ route('password.request') }}" tabindex="-1" class="text-[11px] font-bold text-blue-500 hover:text-blue-700 transition-colors">Lupa sandi?</a>
                    </div>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-300 group-focus-within:text-blue-500 transition-colors">
                            <x-lucide-lock class="w-[18px] h-[18px]" />
                        </div>
                        <input x-bind:type="show ? 'text' : 'password'" id="password" name="password" required
                            class="block w-full pl-11 pr-12 py-3 bg-white border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 focus:bg-white outline-none transition-all placeholder-slate-400 font-bold text-slate-700 tracking-widest"
                            placeholder="••••••••">
                        <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3.5 text-slate-300 hover:text-blue-500 transition-colors focus:outline-none">
                            <template x-if="!show"><x-lucide-eye class="w-[18px] h-[18px]" /></template>
                            <template x-if="show"><x-lucide-eye-off class="w-[18px] h-[18px]" /></template>
                        </button>
                    </div>
                </div>

                <!-- Remember -->
                <label class="flex items-center gap-2.5 cursor-pointer group pt-1">
                    <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500/30 transition-colors shadow-sm">
                    <span class="text-xs font-semibold text-slate-500 group-hover:text-slate-700 transition-colors select-none">Ingat saya di perangkat ini</span>
                </label>

                <!-- Submit -->
                <button type="submit"
                    x-bind:disabled="loading"
                    x-bind:class="loading ? 'opacity-75 cursor-wait' : 'hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-500/25 active:scale-[0.98]'"
                    class="w-full flex items-center justify-center py-3.5 bg-gradient-to-r from-blue-600 to-blue-700 text-white font-bold text-sm rounded-xl transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-blue-500/20 shadow-lg shadow-blue-600/15 mt-2">
                    <span x-show="!loading" class="flex items-center gap-2">
                        Masuk <x-lucide-arrow-right class="w-4 h-4" />
                    </span>
                    <span x-show="loading" x-cloak class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Memproses...
                    </span>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <div class="text-center mt-8 animate-fade-in-up delay-300">
            <p class="text-[10px] font-bold text-slate-300 tracking-wider uppercase leading-relaxed">
                Inspektorat Kabupaten Barito Selatan<br>
                &copy; 2026 &mdash; All Rights Reserved.
            </p>
        </div>
    </div>
</body>
</html>
