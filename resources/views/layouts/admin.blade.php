@extends('layouts.master')

@section('content')
<div class="admin-app page-enter" style="display:flex;min-height:100vh;">
    {{-- Sidebar --}}
    <aside id="sidebar" style="width:252px;background:#0f172a;color:#fff;position:fixed;top:0;bottom:0;left:0;overflow-y:auto;z-index:50;display:flex;flex-direction:column;">
        {{-- Logo --}}
        <div style="padding:18px 20px;border-bottom:1px solid #1e293b;">
            <a href="{{ route('dashboard') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <div>
                    <div style="font-weight:600;font-size:14px;color:#fff;letter-spacing:-0.01em;">Valuasi Ekonomi</div>
                    <div style="font-size:11px;color:#64748b;">Panel Admin</div>
                </div>
            </a>
        </div>

        {{-- Nav --}}
        <nav style="padding:12px;flex:1;">
            <div style="margin-bottom:8px;padding:0 12px;">
                <span style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.08em;">Menu Utama</span>
            </div>

            <a href="{{ route('dashboard') }}" class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                Dashboard
            </a>

            <a href="{{ route('admin.projects.index') }}" class="sidebar-link {{ request()->routeIs('admin.projects.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                Proyek Valuasi
            </a>

            @if(auth()->user()->isAdmin() || auth()->user()->isAnalyst())
            <div style="margin:16px 0 8px;padding:0 12px;">
                <span style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.08em;">Analitik</span>
            </div>

            <a href="{{ route('admin.sensitivity.index') }}" class="sidebar-link {{ request()->routeIs('admin.sensitivity.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20V10"/><path d="M18 20V4"/><path d="M6 20v-4"/></svg>
                Analisis Sensitivitas
            </a>
            @endif

            @if(auth()->user()->isAdmin())
            <div style="margin:16px 0 8px;padding:0 12px;">
                <span style="font-size:11px;font-weight:600;color:#475569;text-transform:uppercase;letter-spacing:0.08em;">Administrasi</span>
            </div>

            <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/><path d="M16 3.13a4 4 0 010 7.75"/></svg>
                Pengguna
            </a>

            <a href="{{ route('admin.master.prices.index') }}" class="sidebar-link {{ request()->routeIs('admin.master.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 7V4h16v3"/><path d="M9 20h6"/><path d="M12 4v16"/></svg>
                Master Data
            </a>

            <a href="{{ route('admin.audit.index') }}" class="sidebar-link {{ request()->routeIs('admin.audit.*') ? 'active' : '' }}">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
                Audit Log
            </a>
            @endif
        </nav>

        {{-- User Info --}}
        <div style="padding:16px;border-top:1px solid #1e293b;">
            <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;">
                <div style="width:34px;height:34px;border-radius:50%;background:#1e293b;border:1px solid #334155;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <span style="color:#cbd5e1;font-weight:600;font-size:12px;">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:13px;font-weight:600;color:#e2e8f0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">{{ auth()->user()->name }}</div>
                    <div style="font-size:11px;color:#64748b;">{{ auth()->user()->role->name ?? '-' }}</div>
                </div>
            </div>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" style="width:100%;padding:8px;background:#1e293b;border:1px solid #334155;border-radius:8px;color:#94a3b8;font-size:13px;font-weight:500;cursor:pointer;transition:all .2s;font-family:inherit;">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- Main Content --}}
    <div style="flex:1;margin-left:252px;">
        {{-- Top Bar --}}
        <header style="background:var(--surface);border-bottom:1px solid var(--border);padding:16px 32px;display:flex;justify-content:space-between;align-items:center;position:sticky;top:0;z-index:40;">
            <div>
                <h1 style="font-size:20px;font-weight:700;color:var(--text);">@yield('page_title', 'Dashboard')</h1>
                @hasSection('page_subtitle')
                    <p style="font-size:13px;color:var(--text-muted);margin-top:2px;">@yield('page_subtitle')</p>
                @endif
            </div>
            <div style="display:flex;align-items:center;gap:16px;">
                <span style="font-size:13px;color:var(--text-muted);">{{ now()->translatedFormat('l, d F Y') }}</span>
                <a href="{{ route('landing') }}" class="btn btn-sm btn-ghost" target="_blank">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><path d="M15 3h6v6"/><path d="M10 14L21 3"/></svg>
                    Situs Publik
                </a>
            </div>
        </header>

        {{-- Content --}}
        <div style="padding:28px 32px;">
            @if($errors->any())
                <div class="alert alert-danger" style="margin-bottom:20px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M15 9l-6 6"/><path d="M9 9l6 6"/></svg>
                    <div>
                        @foreach($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            @if(session('success'))
                <div class="alert alert-success alert-auto-dismiss" style="margin-bottom:20px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @yield('admin_content')
        </div>
    </div>
</div>

<style>
    /* ===== Admin palette — scoped so it never leaks into the public site ===== */
    .admin-app {
        --primary: #4338ca;
        --primary-light: #6366f1;
        --primary-dark: #372aa8;
        --primary-50: #eef1ff;
        --secondary: #0f766e;
        --secondary-dark: #0c5c56;
        --accent: #475569;
        --shadow-sm: 0 1px 2px 0 rgb(15 23 42 / 0.04);
        --shadow: 0 1px 3px 0 rgb(15 23 42 / 0.06), 0 1px 2px -1px rgb(15 23 42 / 0.06);
        --shadow-md: 0 4px 10px -2px rgb(15 23 42 / 0.07);
        --shadow-lg: 0 10px 20px -6px rgb(15 23 42 / 0.10);
        --shadow-glow: none;
    }

    /* Sidebar nav */
    .sidebar-link {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 12px 9px 10px; margin: 1px 0;
        border-left: 2px solid transparent; border-radius: 0 8px 8px 0;
        font-size: 14px; font-weight: 500; color: #94a3b8;
        text-decoration: none; transition: background .15s, color .15s, border-color .15s;
    }
    .sidebar-link:hover { background: #161f32; color: #e2e8f0; }
    .sidebar-link.active { background: rgba(99,102,241,0.12); color: #fff; border-left-color: var(--primary-light); font-weight: 600; }
    .sidebar-link.active svg { stroke: var(--primary-light); }

    /* Flatten the generic component kit for admin only — keep the public site untouched */
    .admin-app .btn::after { content: none; }
    .admin-app .btn-primary:hover,
    .admin-app .btn-secondary:hover,
    .admin-app .btn-accent:hover,
    .admin-app .btn-outline:hover { box-shadow: none; transform: none; }
    .admin-app .card:hover { box-shadow: var(--shadow); }
    .admin-app .stat-card::before { display: none; }
    .admin-app .alert { animation: none; }

    /* Calmer, quicker entrances instead of bouncy staggered slides */
    .admin-app.page-enter,
    .admin-app .animate-fade-up,
    .admin-app .animate-fade-down,
    .admin-app .animate-fade-left,
    .admin-app .animate-fade-right,
    .admin-app .animate-scale,
    .admin-app .stagger > * {
        animation: adminFadeIn .2s ease-out both;
        animation-delay: 0s;
    }
    @keyframes adminFadeIn { from { opacity: 0; } to { opacity: 1; } }
</style>

<script>
    // Format input Rupiah dengan pemisah ribuan "." saat diketik (mis. 50000 -> 50.000),
    // lalu lucuti kembali jadi angka polos sebelum form dikirim ke server.
    document.addEventListener('DOMContentLoaded', function () {
        function digitsOnly(str) {
            return String(str || '').replace(/[^0-9]/g, '');
        }
        function groupThousands(digits) {
            return digits ? digits.replace(/\B(?=(\d{3})+(?!\d))/g, '.') : '';
        }
        function formatInitialValue(raw) {
            var n = parseFloat(raw);
            return isNaN(n) ? '' : groupThousands(String(Math.round(n)));
        }

        document.querySelectorAll('.rupiah-input').forEach(function (el) {
            el.type = 'text';
            el.setAttribute('inputmode', 'numeric');
            el.setAttribute('autocomplete', 'off');
            el.value = formatInitialValue(el.value);

            el.addEventListener('input', function () {
                var digitsBeforeCursor = digitsOnly(el.value.slice(0, el.selectionStart)).length;
                el.value = groupThousands(digitsOnly(el.value));

                var pos = 0, seen = 0;
                while (pos < el.value.length && seen < digitsBeforeCursor) {
                    if (/\d/.test(el.value[pos])) seen++;
                    pos++;
                }
                el.setSelectionRange(pos, pos);
            });

            var form = el.closest('form');
            if (form) {
                form.addEventListener('submit', function () {
                    el.value = digitsOnly(el.value);
                });
            }
        });
    });
</script>
@endsection
