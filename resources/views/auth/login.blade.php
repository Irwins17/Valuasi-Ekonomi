@extends('layouts.master')
@section('title', 'Sign in to Valek')

@section('styles')
<style>
    /* (Blue Glassmorphism Edition) ===== */
    .dc-login {
        /* Tema Terang / Glass */
        --dc-panel-bg: rgba(255, 255, 255, 0.65);
        --dc-heading: #0f2c59;
        --dc-muted: #4b6584;
        --dc-divider: rgba(15, 44, 89, 0.12);
        --dc-input-bg: rgba(255, 255, 255, 0.8);
        --dc-input-border: rgba(15, 44, 89, 0.2);
        --dc-input-text: #1e272e;
        --dc-toggle-bg: #2563eb;
        --dc-knob-shift: translateX(0);
        --dc-accent: #2563eb; /* Warna Biru Utama */
        --dc-accent-hover: #1d4ed8;
        --dc-btn-bg: linear-gradient(180deg, #3b82f6 0%, #1d4ed8 100%);
        --dc-btn-shadow: rgba(37, 99, 235, 0.35);
        --dc-card-border: rgba(255, 255, 255, 0.6);
    }

    .dc-login.dark {
        /* Tema Gelap / Dark Glass */
        --dc-panel-bg: rgba(15, 23, 42, 0.75);
        --dc-heading: #f8fafc;
        --dc-muted: #94a3b8;
        --dc-divider: rgba(255, 255, 255, 0.12);
        --dc-input-bg: rgba(30, 41, 59, 0.8);
        --dc-input-border: rgba(255, 255, 255, 0.15);
        --dc-input-text: #f1f5f9;
        --dc-toggle-bg: #3b82f6;
        --dc-knob-shift: translateX(20px);
        --dc-accent: #3b82f6;
        --dc-accent-hover: #60a5fa;
        --dc-btn-bg: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
        --dc-btn-shadow: rgba(0, 0, 0, 0.4);
        --dc-card-border: rgba(255, 255, 255, 0.15);
    }

    /* Latar belakang halaman menggunakan gambar penuh */
    .dc-login {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 32px 24px;
        box-sizing: border-box;
        background-image: url("{{ asset('images/background.png') }}");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        position: relative;
    }

    /* Efek transparan/blur pada kartu utama (Glassmorphism) */
    .dc-card {
        width: 100%;
        max-width: 1020px;
        display: grid;
        grid-template-columns: 1.15fr 1fr;
        align-items: stretch;
        border-radius: 28px;
        overflow: hidden;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.3);
        border: 1px solid var(--dc-card-border);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        background: var(--dc-panel-bg);
        transition: background 0.3s ease, border-color 0.3s ease;
    }

    /* Kolom gambar di sebelah kiri dibuat sedikit lebih transparan/menyatu */
    .dc-art {
        position: relative;
        min-height: 100%;
        padding: 16px;
        display: flex;
    }
    .dc-art img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
        display: block;
        border-radius: 18px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.15);
    }

    /* Panel form sebelah kanan */
    .dc-panel {
        padding: 34px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: transparent; /* Agar mengikuti warna kaca kartu utama */
    }

    .dc-brand { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .dc-brand-name { display: flex; align-items: center; gap: 6px; }
    .dc-brand-name span:first-child { font-weight: 800; font-size: 16px; color: var(--dc-heading); letter-spacing: -0.2px; }
    .dc-brand-dot { width: 8px; height: 8px; border-radius: 50%; background: var(--dc-accent); display: inline-block; margin-top: 2px; }

    .dc-toggle {
        width: 46px; height: 26px; border-radius: 13px; border: none; cursor: pointer; padding: 3px;
        box-sizing: border-box; background: var(--dc-toggle-bg); display: flex; transition: background 0.25s ease;
    }
    .dc-toggle-knob { width: 20px; height: 20px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(0,0,0,0.25); transform: var(--dc-knob-shift); transition: transform 0.25s ease; }

    .dc-rule { height: 1px; background: var(--dc-divider); }
    .dc-title { margin: 0; font-size: 26px; font-weight: 800; letter-spacing: -0.5px; color: var(--dc-heading); line-height: 1.2; }
    .dc-subtitle { margin: 10px 0 0; font-size: 13.5px; color: var(--dc-muted); }
    .dc-subtitle a { color: var(--dc-accent); font-weight: 700; }

    .dc-fields { display: flex; flex-direction: column; gap: 16px; }
    .dc-input {
        height: 50px; border-radius: 12px; border: 1px solid var(--dc-input-border); background: var(--dc-input-bg);
        padding: 0 18px; font-size: 13.5px; font-weight: 500; color: var(--dc-input-text);
        outline: none; box-sizing: border-box; width: 100%; font-family: inherit;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .dc-input:focus { border-color: var(--dc-accent); box-shadow: 0 0 0 3px var(--dc-btn-shadow); background: rgba(255,255,255,0.95); }
    .dc-input::placeholder { color: var(--dc-muted); }
    
    /* Pengecualian focus warna latar untuk dark mode */
    .dc-login.dark .dc-input:focus { background: rgba(30, 41, 59, 0.95); }

    .dc-pw-wrap { position: relative; }
    .dc-pw-wrap .dc-input { padding-right: 46px; }
    .dc-pw-toggle {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 36px; height: 36px; border: none; background: transparent; cursor: pointer;
        display: flex; align-items: center; justify-content: center; color: var(--dc-muted);
    }

    .dc-remember { display: flex; align-items: center; gap: 8px; margin-top: 16px; font-size: 13px; color: var(--dc-muted); }
    .dc-remember input { accent-color: var(--dc-accent); cursor: pointer; }
    .dc-remember label { cursor: pointer; }

    .dc-submit {
        margin-top: 24px; height: 50px; width: 100%; border: none; border-radius: 12px; cursor: pointer;
        font-family: inherit; font-size: 14px; font-weight: 700; color: #fff;
        background: var(--dc-btn-bg); box-shadow: 0 10px 22px var(--dc-btn-shadow); transition: filter 0.15s ease, transform 0.1s ease;
    }
    .dc-submit:hover { filter: brightness(1.08); }
    .dc-submit:active { transform: scale(0.99); }

    .dc-demo-label { display: flex; align-items: center; gap: 12px; margin: 22px 0 12px; color: var(--dc-muted); font-size: 11px; font-weight: 700; letter-spacing: 1px; justify-content: center; }
    .dc-demo-label span:first-child, .dc-demo-label span:last-child { width: 18px; height: 1px; background: var(--dc-muted); opacity: 0.5; }
    .dc-demo-hint { text-align: center; font-size: 12px; color: var(--dc-muted); font-weight: 600; }

    .dc-back { text-align: center; margin-top: 18px; }
    .dc-back a { font-size: 13px; color: var(--dc-accent); font-weight: 700; text-decoration: none; }
    .dc-back a:hover { color: var(--dc-accent-hover); text-decoration: underline; }

    .dc-alert {
        margin-top: 20px; padding: 12px 16px; border-radius: 12px; font-size: 13px; font-weight: 600;
        background: rgba(239, 68, 68, 0.15);
        color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3);
    }

    /* Tablet / smaller desktop */
    @media (max-width: 1000px) {
        .dc-card { max-width: 880px; grid-template-columns: 1fr 1fr; }
        .dc-panel { padding: 30px 32px; }
    }

    /* Mobile: Stack vertically */
    @media (max-width: 720px) {
        .dc-login { padding: 20px 14px; }
        .dc-card { grid-template-columns: 1fr; max-width: 460px; border-radius: 22px; }
        .dc-art { min-height: 0; height: 180px; padding: 12px 12px 0 12px; }
        .dc-art img { border-radius: 14px; object-position: center 62%; }
        .dc-panel { padding: 26px 24px 30px; }
        .dc-title { font-size: 23px; }
    }

    @media (max-width: 380px) {
        .dc-panel { padding: 22px 18px 26px; }
        .dc-art { height: 140px; }
    }
</style>
@endsection

@section('content')
<div class="dc-login" id="dcLogin">
    <div class="dc-card animate-scale">
        {{-- Illustration --}}
        <div class="dc-art">
            <img src="{{ asset('images/pemandangan.png') }}" alt="Ilustrasi danau dengan perahu dan pegunungan">
        </div>

        {{-- Form panel --}}
        <div class="dc-panel">
            <div class="dc-brand">
                <div class="dc-brand-name">
                    <span>Valuasi Ekonomi</span>
                    <span class="dc-brand-dot"></span>
                </div>
                <button type="button" class="dc-toggle" id="dcDarkToggle" aria-label="Ganti mode gelap">
                    <span class="dc-toggle-knob"></span>
                </button>
            </div>

            <div class="dc-rule" style="margin: 18px 0 22px;"></div>

            <h1 class="dc-title">Sign in to Valek</h1>
            <p class="dc-subtitle">Portal Administrasi Data Valuasi Ekonomi Sumber Daya Alam</p>

            <div class="dc-rule" style="margin: 20px 0 24px;"></div>

            @if($errors->any())
            <div class="dc-alert" style="margin-bottom: 20px; margin-top: 0;">
                {{ $errors->first('email') ?: 'Email atau password tidak sesuai' }}
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                <div class="dc-fields">
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="dc-input" placeholder="Alamat email" aria-label="Email">
                    <div class="dc-pw-wrap">
                        <input type="password" id="password" name="password" required
                               class="dc-input" placeholder="Password" aria-label="Password">
                        <button type="button" class="dc-pw-toggle" id="togglePasswordBtn" aria-label="Tampilkan password">
                            <svg id="eyeIcon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg id="eyeOffIcon" style="display:none" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0112 20c-7 0-11-8-11-8a21.8 21.8 0 015.06-6.06M9.9 4.24A10.4 10.4 0 0112 4c7 0 11 8 11 8a21.7 21.7 0 01-2.16 3.19M14.12 14.12a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <div class="dc-remember">
                    <input type="checkbox" id="remember" name="remember">
                    <label for="remember">Ingat saya</label>
                </div>

                <button type="submit" class="dc-submit">Masuk</button>
            </form>

            <div class="dc-demo-label"><span></span><span>Akun Demo</span><span></span></div>
            <div class="dc-demo-hint">admin@valuasi.local &nbsp;/&nbsp; admin@123</div>

            <div class="dc-back">
                <a href="{{ route('landing') }}">&larr; Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Password visibility toggle
    document.getElementById('togglePasswordBtn').addEventListener('click', function () {
        const input = document.getElementById('password');
        const eye = document.getElementById('eyeIcon');
        const eyeOff = document.getElementById('eyeOffIcon');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        eye.style.display = show ? 'none' : 'block';
        eyeOff.style.display = show ? 'block' : 'none';
        this.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });

    // Dark mode toggle (scoped to the login screen, remembered per browser)
    (function () {
        const root = document.getElementById('dcLogin');
        const toggle = document.getElementById('dcDarkToggle');
        if (localStorage.getItem('dcLoginDark') === '1') root.classList.add('dark');
        toggle.addEventListener('click', function () {
            const dark = root.classList.toggle('dark');
            localStorage.setItem('dcLoginDark', dark ? '1' : '0');
        });
    })();
</script>
@endsection