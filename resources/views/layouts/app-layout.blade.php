<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'SIMPEL-KETAATAN') }} | Sistem Penyusunan dan Pelaporan LHP Audit Ketaatan</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 text-slate-800 font-sans antialiased overflow-x-hidden relative min-h-screen selection:bg-blue-500/30" x-data="{ sidebarOpen: true, notificationsOpen: false }">
    
    <!-- Ambient Background -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none -z-10">
        <div class="absolute inset-0 bg-slate-50"></div>
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-blue-50"></div>
        <div class="absolute bottom-[10%] -right-[10%] w-[60%] h-[60%] rounded-full bg-emerald-50"></div>
    </div>
    <!-- Sidebar -->
    @include('layouts.sidebar')

    <!-- Main Content -->
    <div class="transition-all duration-300 flex flex-col min-h-screen" :class="sidebarOpen ? 'lg:pl-64' : 'pl-0'">
        <!-- Top Header -->
        <header class="h-16 bg-white border-b border-slate-200 sticky top-0 z-40 px-4 sm:px-6 lg:px-8 flex items-center justify-between">
            <button @click="sidebarOpen = !sidebarOpen" class="text-slate-500 hover:text-slate-700 focus:outline-none p-2 rounded-lg bg-slate-50 hover:bg-slate-100 transition-colors">
                <x-lucide-menu class="w-5 h-5" />
            </button>
            @php
                $notificationQuery = auth()->user()->notifications()->latest()->limit(8);
                $headerNotifications = $notificationQuery
                    ->select('*')
                    ->selectSub(
                        auth()->user()->notifications()->whereNull('read_at')->selectRaw('COUNT(*)'),
                        'unread_total'
                    )
                    ->get();
                $headerUnreadCount = (int) ($headerNotifications->first()->unread_total ?? 0);
            @endphp
            <div class="flex items-center gap-4">
                <div class="relative" @click.away="notificationsOpen = false">
                    <button
                        type="button"
                        @click="notificationsOpen = !notificationsOpen"
                        class="text-slate-400 hover:text-slate-600 relative p-2 rounded-lg hover:bg-slate-100 transition-colors"
                        aria-label="Notifikasi"
                    >
                        <x-lucide-bell class="w-5 h-5" />
                        @if($headerUnreadCount > 0)
                            <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center border-2 border-white">
                                {{ $headerUnreadCount > 99 ? '99+' : $headerUnreadCount }}
                            </span>
                        @endif
                    </button>

                    <div
                        x-show="notificationsOpen"
                        x-transition.origin.top.right
                        class="absolute right-0 mt-2 w-96 max-w-[90vw] bg-white border border-slate-200 rounded-2xl shadow-xl overflow-hidden z-50"
                        style="display: none;"
                    >
                        <div class="px-4 py-3 border-b border-slate-100 flex items-center justify-between bg-slate-50/80">
                            <p class="text-sm font-bold text-slate-800">Notifikasi Workflow</p>
                            <span class="text-[11px] font-semibold text-slate-500">{{ $headerUnreadCount }} belum dibaca</span>
                        </div>

                        @if($headerNotifications->isEmpty())
                            <div class="px-4 py-8 text-center text-sm text-slate-500">
                                Belum ada notifikasi baru.
                            </div>
                        @else
                            <div class="max-h-96 overflow-y-auto divide-y divide-slate-100">
                                @foreach($headerNotifications as $notification)
                                    <a
                                        href="{{ route('notifications.read', $notification->id) }}"
                                        class="block px-4 py-3 hover:bg-slate-50 transition-colors {{ is_null($notification->read_at) ? 'bg-blue-50/40' : 'bg-white' }}"
                                    >
                                        <p class="text-sm text-slate-700 leading-snug">{{ $notification->data['message'] ?? 'Notifikasi baru.' }}</p>
                                        <p class="text-[11px] text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                                    </a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Area -->
        <main class="flex-1 p-6 md:p-8">
            <div class="max-w-7xl mx-auto">
                @yield('content')
            </div>
        </main>
    </div>

    <!-- Global SweetAlert Toasts -->
    @if(session('success') || session('error') || session('warning') || session('info'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: {
                    popup: 'bg-white border border-slate-100 shadow-xl rounded-2xl',
                    title: 'text-sm font-bold text-slate-700 font-sans'
                },
                didOpen: (toast) => {
                    toast.onmouseenter = Swal.stopTimer;
                    toast.onmouseleave = Swal.resumeTimer;
                }
            });

            @if(session('success'))
            Toast.fire({ icon: 'success', title: {!! json_encode(session('success')) !!} });
            @endif

            @if(session('error'))
            Toast.fire({ icon: 'error', title: {!! json_encode(session('error')) !!} });
            @endif

            @if(session('warning'))
            Toast.fire({ icon: 'warning', title: {!! json_encode(session('warning')) !!} });
            @endif

            @if(session('info'))
            Toast.fire({ icon: 'info', title: {!! json_encode(session('info')) !!} });
            @endif
        });
    </script>
    @endif

    @stack('scripts')
</body>
</html>
