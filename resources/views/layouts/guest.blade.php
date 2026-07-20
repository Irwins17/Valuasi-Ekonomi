@extends('layouts.master')

@section('content')
<div class="page-enter">
    {{-- Navbar --}}
    <nav id="main-nav" style="position:fixed;top:0;left:0;right:0;z-index:100;transition:all .3s ease;">
        <div style="max-width:1280px;margin:0 auto;padding:16px 24px;display:flex;justify-content:space-between;align-items:center;">
            <a href="{{ route('landing') }}" style="display:flex;align-items:center;gap:10px;text-decoration:none;">
                <span style="font-weight:700;font-size:18px;color:var(--text);" class="nav-brand-text">Valuasi Ekonomi</span>
            </a>

            <div style="display:flex;align-items:center;gap:32px;" class="hide-mobile">
                <a href="{{ route('landing') }}" style="font-weight:500;font-size:14px;color:var(--text-secondary);text-decoration:none;position:relative;" class="nav-link {{ request()->routeIs('landing') ? 'active' : '' }}">Beranda</a>
                <a href="{{ route('public.dashboard') }}" style="font-weight:500;font-size:14px;color:var(--text-secondary);text-decoration:none;" class="nav-link {{ request()->routeIs('public.dashboard') ? 'active' : '' }}">Dashboard</a>
                <a href="{{ route('public.glossary') }}" style="font-weight:500;font-size:14px;color:var(--text-secondary);text-decoration:none;" class="nav-link {{ request()->routeIs('public.glossary') ? 'active' : '' }}">Edukasi</a>
            </div>

            <div style="display:flex;align-items:center;gap:12px;">
                @auth
                    <span style="font-size:14px;color:var(--text-secondary);">{{ auth()->user()->name }}</span>
                    <a href="{{ route('dashboard') }}" class="btn btn-sm btn-primary">Dashboard</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary">Masuk</a>
                @endauth
            </div>
        </div>
    </nav>

    {{-- Main --}}
    <main style="margin-top:74px;">
        @yield('guest_content')
    </main>

    {{-- Footer --}}
    <footer style="background:#0f172a;color:#fff;padding:60px 0 0;">
        <div style="max-width:1280px;margin:0 auto;padding:0 24px;">
            <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:40px;margin-bottom:40px;">
                <div>
                    <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;">
                        <span style="font-weight:700;font-size:16px;">Valuasi Ekonomi</span>
                    </div>
                    <p style="color:#94a3b8;font-size:14px;line-height:1.7;">Platform transparansi data valuasi ekonomi untuk kebijakan berkelanjutan dan investasi publik.</p>
                </div>
                <div>
                    <h4 style="font-weight:700;margin-bottom:16px;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">Navigasi</h4>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;">
                        <li><a href="{{ route('landing') }}" style="color:#94a3b8;text-decoration:none;font-size:14px;transition:color .2s;">Beranda</a></li>
                        <li><a href="{{ route('public.dashboard') }}" style="color:#94a3b8;text-decoration:none;font-size:14px;">Dashboard Publik</a></li>
                        <li><a href="{{ route('public.glossary') }}" style="color:#94a3b8;text-decoration:none;font-size:14px;">Glosarium & Edukasi</a></li>
                    </ul>
                </div>
                <div>
                    <h4 style="font-weight:700;margin-bottom:16px;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">Metodologi</h4>
                    <ul style="list-style:none;display:flex;flex-direction:column;gap:10px;">
                        <li><span style="color:#94a3b8;font-size:14px;">Travel Cost Method (TCM)</span></li>
                        <li><span style="color:#94a3b8;font-size:14px;">Contingent Valuation (CVM)</span></li>
                        <li><span style="color:#94a3b8;font-size:14px;">Effect on Production (EOP)</span></li>
                    </ul>
                </div>
                <div>
                    <h4 style="font-weight:700;margin-bottom:16px;font-size:14px;text-transform:uppercase;letter-spacing:0.05em;color:#64748b;">Kontak</h4>
                    <p style="color:#94a3b8;font-size:14px;">Email: info@valuasi.local</p>
                    <p style="color:#94a3b8;font-size:14px;margin-top:8px;">Telp: (021) 123-4567</p>
                </div>
            </div>
            <div style="border-top:1px solid #1e293b;padding:24px 0;text-align:center;">
                <p style="color:#64748b;font-size:13px;">&copy; {{ date('Y') }} Valuasi Ekonomi. Semua hak dilindungi.</p>
            </div>
        </div>
    </footer>
</div>

<style>
    #main-nav { background: rgba(255,255,255,0.85); backdrop-filter: blur(20px); border-bottom: 1px solid var(--border-light); }
    #main-nav.scrolled { background: rgba(255,255,255,0.95); box-shadow: var(--shadow-md); }
    .nav-link:hover { color: var(--primary) !important; }
    .nav-link.active { color: var(--primary) !important; }
    .nav-link.active::after { content:''; position:absolute; bottom:-4px; left:0; right:0; height:2px; background:var(--primary); border-radius:2px; }
</style>

<script>
    window.addEventListener('scroll', () => {
        const nav = document.getElementById('main-nav');
        if (window.scrollY > 50) nav.classList.add('scrolled');
        else nav.classList.remove('scrolled');
    });
</script>
@endsection
