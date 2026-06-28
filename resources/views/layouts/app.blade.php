<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | Sistem Pensiun</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    @yield('styles')
    <style>
        :root {
            --primary:   #1A5632;
            --accent:    #27AE60;
            --success:   #1E8449;
            --warning:   #D4AC0D;
            --danger:    #C0392B;
            --sidebar-w: 260px;
        }
        body { background:#f0f4f8; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,sans-serif; min-height:100vh; }
        #sidebar { width:var(--sidebar-w); min-height:100vh; background:var(--primary); display:flex; flex-direction:column; flex-shrink:0; position:fixed; top:0; left:0; height:100vh; overflow-y:auto; z-index:1040; transition:transform 0.3s ease; }
        #sidebar .sidebar-brand { padding:1.5rem 1.25rem 1rem; border-bottom:1px solid rgba(255,255,255,0.12); }
        #sidebar .sidebar-brand h6 { color:rgba(255,255,255,0.6); font-size:0.7rem; text-transform:uppercase; letter-spacing:1px; margin-bottom:0.35rem; }
        #sidebar .sidebar-brand .brand-name { color:#fff; font-weight:700; font-size:0.95rem; line-height:1.3; }
        #sidebar .user-info { padding:1rem 1.25rem; border-bottom:1px solid rgba(255,255,255,0.12); background:rgba(0,0,0,0.15); }
        #sidebar .user-info .user-name { color:#fff; font-weight:600; font-size:0.9rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        #sidebar .user-info .user-role-badge { font-size:0.7rem; padding:0.2rem 0.55rem; border-radius:999px; background:rgba(255,255,255,0.18); color:#fff; display:inline-block; margin-top:0.3rem; }
        #sidebar .nav-section-label { color:rgba(255,255,255,0.45); font-size:0.65rem; text-transform:uppercase; letter-spacing:1.2px; padding:1rem 1.25rem 0.4rem; }
        #sidebar .nav-link { color:rgba(255,255,255,0.78); padding:0.6rem 1.25rem; display:flex; align-items:center; gap:0.65rem; font-size:0.88rem; border-radius:0; transition:background 0.15s,color 0.15s; text-decoration:none; }
        #sidebar .nav-link i { font-size:1rem; width:20px; text-align:center; flex-shrink:0; }
        #sidebar .nav-link:hover { background:rgba(255,255,255,0.1); color:#fff; }
        #sidebar .nav-link.active { background:var(--accent); color:#fff; font-weight:600; }
        #sidebar .sidebar-footer { margin-top:auto; padding:1rem 1.25rem; border-top:1px solid rgba(255,255,255,0.12); }
        #sidebar .btn-logout { width:100%; background:rgba(192,57,43,0.75); color:#fff; border:none; border-radius:6px; padding:0.5rem; font-size:0.85rem; display:flex; align-items:center; justify-content:center; gap:0.5rem; cursor:pointer; transition:background 0.15s; }
        #sidebar .btn-logout:hover { background:var(--danger); }
        #wrapper { display:flex; min-height:100vh; }
        #main-content { margin-left:var(--sidebar-w); flex:1; display:flex; flex-direction:column; min-width:0; }
        .topbar { background:#fff; border-bottom:1px solid #dee2e6; padding:0.75rem 1.5rem; display:flex; align-items:center; gap:1rem; position:sticky; top:0; z-index:100; box-shadow:0 1px 4px rgba(0,0,0,0.06); }
        .topbar .page-title { font-weight:600; font-size:1rem; color:var(--primary); margin:0; }
        .topbar .topbar-right { margin-left:auto; display:flex; align-items:center; gap:0.75rem; }
        .topbar .topbar-right .user-text { font-size:0.85rem; color:#555; }
        .btn-sidebar-toggle { display:none; background:none; border:none; font-size:1.25rem; color:var(--primary); cursor:pointer; padding:0; line-height:1; }
        .page-body { padding:1.75rem; flex:1; }
        .card { border:none; border-radius:10px; box-shadow:0 1px 6px rgba(0,0,0,0.07); }
        .card-header { background:#fff; border-bottom:1px solid #f0f0f0; font-weight:600; color:var(--primary); padding:1rem 1.25rem; border-radius:10px 10px 0 0 !important; }
        .stat-card { border-radius:10px; padding:1.25rem; color:#fff; display:flex; align-items:center; gap:1rem; }
        .stat-card .stat-icon { font-size:2rem; opacity:0.85; }
        .stat-card .stat-value { font-size:2rem; font-weight:700; line-height:1; }
        .stat-card .stat-label { font-size:0.8rem; opacity:0.85; margin-top:0.2rem; }
        @media (max-width:991px) {
            #sidebar { transform:translateX(calc(-1 * var(--sidebar-w))); }
            #sidebar.open { transform:translateX(0); box-shadow:4px 0 20px rgba(0,0,0,0.3); }
            #main-content { margin-left:0; }
            .btn-sidebar-toggle { display:block; }
            #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:1039; }
            #sidebar-overlay.show { display:block; }
        }
        .table th { font-size:0.8rem; text-transform:uppercase; letter-spacing:0.5px; color:#6c757d; font-weight:600; white-space:nowrap; }
        .btn-primary { background:var(--accent); border-color:var(--accent); }
        .btn-primary:hover { background:var(--primary); border-color:var(--primary); }
        .page-header { margin-bottom:1.5rem; }
        .page-header h4 { color:var(--primary); font-weight:700; margin:0; }
    </style>
</head>
<body>

<div id="sidebar-overlay"></div>

<div id="wrapper">
    <nav id="sidebar">

        {{-- Brand --}}
        <div class="sidebar-brand">
            <h6>Instansi Pemerintah</h6>
            <div class="brand-name">
                <i class="bi bi-award-fill me-1" style="color:#F9E79F"></i>
                Sistem Informasi<br>Pengajuan Pensiun
            </div>
        </div>

        {{-- User Info --}}
        <div class="user-info">
            <div class="d-flex align-items-center gap-2 mb-1">
                <div style="width:32px;height:32px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;flex-shrink:0">
                    <i class="bi bi-person-fill text-white" style="font-size:1rem"></i>
                </div>
                <div style="overflow:hidden">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                </div>
            </div>
            <span class="user-role-badge">{{ auth()->user()->role->label() }}</span>
            @if(auth()->user()->office)
                <div style="color:rgba(255,255,255,0.55);font-size:0.72rem;margin-top:0.3rem">
                    <i class="bi bi-building me-1"></i>{{ auth()->user()->office }}
                </div>
            @endif
        </div>

        {{-- Navigation --}}
        <div style="flex:1">
            <div class="nav-section-label">Menu Utama</div>

            {{-- Dashboard: semua role --}}
            <a href="{{ route('dashboard') }}"
               class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-house-fill"></i> Dashboard
            </a>

            {{-- Pensiunan: hanya artikel & regulasi, tidak perlu lihat pengajuan (sudah di dashboard) --}}
           @if(auth()->user()->isPensiunan())

    <a href="{{ route('applications.index') }}"
       class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
        <i class="bi bi-folder2-open"></i> Pengajuan Pensiun
    </a>

    <a href="{{ route('articles.index') }}"
       class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}">
        <i class="bi bi-newspaper"></i> Artikel MPP
    </a>

    <a href="{{ route('regulations.index') }}"
       class="nav-link {{ request()->routeIs('regulations.*') ? 'active' : '' }}">
        <i class="bi bi-file-earmark-text-fill"></i> Regulasi &amp; UU
    </a>

            {{-- SDM Kantor --}}
            @elseif(auth()->user()->isSdmKantor())

                <a href="{{ route('pension-types.index') }}"
                   class="nav-link {{ request()->routeIs('pension-types.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-bookmark-fill"></i> Jenis Pensiun
                </a>

                <a href="{{ route('applications.index') }}"
                   class="nav-link {{ request()->routeIs('applications.*') && !request()->routeIs('applications.create') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i> Daftar Pengajuan
                </a>

                <a href="{{ route('articles.index') }}"
                   class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}">
                    <i class="bi bi-newspaper"></i> Artikel MPP
                </a>

                <a href="{{ route('regulations.index') }}"
                   class="nav-link {{ request()->routeIs('regulations.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Regulasi &amp; UU
                </a>

                <div class="nav-section-label">Aksi</div>

                <a href="{{ route('applications.create') }}"
                   class="nav-link {{ request()->routeIs('applications.create') ? 'active' : '' }}">
                    <i class="bi bi-plus-circle-fill"></i> Buat Pengajuan Baru
                </a>

            {{-- SDM Kanwil & TIK --}}
            @else

                <a href="{{ route('pension-types.index') }}"
                   class="nav-link {{ request()->routeIs('pension-types.*') ? 'active' : '' }}">
                    <i class="bi bi-journal-bookmark-fill"></i> Jenis Pensiun
                </a>

                <a href="{{ route('applications.index') }}"
                   class="nav-link {{ request()->routeIs('applications.*') ? 'active' : '' }}">
                    <i class="bi bi-folder2-open"></i> Pengajuan Pensiun
                </a>

                <a href="{{ route('articles.index') }}"
                   class="nav-link {{ request()->routeIs('articles.*') ? 'active' : '' }}">
                    <i class="bi bi-newspaper"></i> Artikel MPP
                </a>

                <a href="{{ route('regulations.index') }}"
                   class="nav-link {{ request()->routeIs('regulations.*') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-text-fill"></i> Regulasi &amp; UU
                </a>

                <div class="nav-section-label">Pengelolaan</div>

                <a href="{{ route('applications.index') }}?status=verifikasi"
                   class="nav-link">
                    <i class="bi bi-clipboard2-check-fill"></i> Verifikasi Pengajuan
                </a>

                <a href="{{ route('articles.create') }}"
                   class="nav-link {{ request()->routeIs('articles.create') ? 'active' : '' }}">
                    <i class="bi bi-pencil-square"></i> Tulis Artikel
                </a>

                <a href="{{ route('regulations.create') }}"
                   class="nav-link {{ request()->routeIs('regulations.create') ? 'active' : '' }}">
                    <i class="bi bi-file-earmark-plus-fill"></i> Tambah Regulasi
                </a>

                {{-- Hanya TIK --}}
                @if(auth()->user()->isTik())
                    <div class="nav-section-label">Administrasi</div>
                    <a href="{{ route('users.index') }}"
                       class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Manajemen User
                    </a>
                @endif

            @endif
        </div>

        {{-- Logout --}}
        <div class="sidebar-footer">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="bi bi-box-arrow-left"></i> Keluar
                </button>
            </form>
        </div>
    </nav>

    <div id="main-content">
        <header class="topbar">
            <button class="btn-sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            <div class="topbar-right">
                <span class="user-text d-none d-md-inline">
                    <i class="bi bi-person-circle me-1"></i>{{ auth()->user()->name }}
                </span>
                <span class="badge" style="background:var(--primary)">
                    {{ auth()->user()->role->label() }}
                </span>
            </div>
        </header>
        <main class="page-body">
            @include('partials.alerts')
            @yield('content')
        </main>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    const sidebarToggle  = document.getElementById('sidebarToggle');
    const sidebar        = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    function openSidebar()  { sidebar.classList.add('open');    sidebarOverlay.classList.add('show'); }
    function closeSidebar() { sidebar.classList.remove('open'); sidebarOverlay.classList.remove('show'); }
    if (sidebarToggle) sidebarToggle.addEventListener('click', () => sidebar.classList.contains('open') ? closeSidebar() : openSidebar());
    sidebarOverlay.addEventListener('click', closeSidebar);
</script>
@yield('scripts')
</body>
</html>
