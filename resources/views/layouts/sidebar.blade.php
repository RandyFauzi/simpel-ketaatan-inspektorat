<aside class="fixed inset-y-0 left-0 z-50 flex flex-col w-64 bg-white border-r border-slate-200 transition-transform duration-300 shadow-sm"
       :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">
    <div class="flex items-start justify-between h-20 px-6 py-4 border-b border-slate-100">
        <div class="flex items-center gap-3">
            <img src="{{ asset('logo.webp') }}" class="w-10 h-10 object-contain drop-shadow-sm shrink-0" alt="Logo">
            <div class="flex flex-col">
                <span class="text-sm font-bold text-primary-blue leading-tight tracking-wide">SIMPEL-KETAATAN</span>
                <span class="text-[9px] text-slate-500 leading-tight mt-0.5 font-medium">Sistem Penyusunan dan<br>Pelaporan LHP Audit Ketaatan</span>
            </div>
        </div>
        <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-slate-600 mt-1">
            <x-lucide-x class="w-5 h-5" />
        </button>
    </div>
    
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        @php
            if (!function_exists('isActive')) {
                function isActive($route) {
                    return request()->routeIs($route) ? 'bg-blue-50 text-blue-600 font-medium' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900 transition-colors';
                }
            }
        @endphp
    
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ isActive('dashboard') }}">
            <x-lucide-layout-dashboard class="w-5 h-5" /> Dashboard
        </a>
        
        @if(auth()->check() && auth()->user()->role !== 'skpd')
        <!-- Admin & Auditor Menus -->
        <a href="{{ route('lhp.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ isActive('lhp.*') }}">
            <x-lucide-file-text class="w-5 h-5" /> Daftar LHP
        </a>
        @if(in_array(auth()->user()->role, ['admin', 'auditor']))
        <a href="{{ route('auditor.lhp.create') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ isActive('auditor.lhp.create') }}">
            <x-lucide-file-plus class="w-5 h-5" /> Buat LHP Baru
        </a>
        @endif
        @if(auth()->user()->role === 'admin')
        <div class="pt-2 mt-2 border-t border-slate-100">
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ isActive('users.*') }}">
                <x-lucide-users class="w-5 h-5" /> Manajemen Akun
            </a>
        </div>
        @endif
        @else
        <!-- SKPD Menus -->
        <a href="{{ route('lhp.index') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ isActive('lhp.*') }}">
            <x-lucide-file-text class="w-5 h-5" /> Daftar LHP
        </a>
        <a href="#" class="flex items-center gap-3 px-3 py-2.5 rounded-xl {{ isActive('skpd.followup.*') }}">
            <x-lucide-inbox class="w-5 h-5" /> Tindak Lanjut
        </a>
        @endif
    </nav>
    
    <div class="p-4 border-t border-slate-100 bg-slate-50 mt-auto flex flex-col gap-2">
        <div class="flex items-center gap-3 px-3 py-2">
            <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-700 font-bold uppercase shadow-sm border border-blue-200">
                {{ substr(auth()->user()?->name ?? 'G', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-slate-900 truncate">{{ auth()->user()?->name ?? 'Guest User' }}</p>
                <p class="text-[10px] text-slate-500 truncate font-extrabold uppercase tracking-wide">{{ str_replace('_', ' ', auth()->user()?->role ?? 'viewer') }}</p>
            </div>
            <a href="{{ route('profile') }}" class="p-2 text-slate-400 hover:text-blue-600 hover:bg-blue-50 rounded-xl transition-all" title="Edit Profil">
                <x-lucide-settings class="w-4 h-4" />
            </a>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="flex w-full items-center justify-center gap-2 px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-xs font-bold text-slate-600 hover:text-red-600 hover:bg-red-50 hover:border-red-100 transition-colors shadow-sm">
                <x-lucide-log-out class="w-4 h-4" /> Keluar Sistem
            </button>
        </form>
    </div>
</aside>
