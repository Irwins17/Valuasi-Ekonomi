@extends('layouts.guest')
@section('title', 'Valuasi Ekonomi — Platform Transparansi Data')
@section('guest_content')

{{-- Hero Section --}}
<section style="background:linear-gradient(135deg,#1e1b4b 0%,#312e81 30%,#4f46e5 60%,#06d6a0 100%);background-size:300% 300%;animation:gradientShift 10s ease infinite;padding:100px 0 80px;position:relative;overflow:hidden;">
    <div style="position:absolute;inset:0;background:url('data:image/svg+xml,%3Csvg width=%2260%22 height=%2260%22 viewBox=%220 0 60 60%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cg fill=%22none%22 fill-rule=%22evenodd%22%3E%3Cg fill=%22%23ffffff%22 fill-opacity=%220.05%22%3E%3Ccircle cx=%2230%22 cy=%2230%22 r=%221.5%22/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;position:relative;z-index:1;">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:60px;align-items:center;">
            <div class="animate-fade-left">
                <div class="badge" style="background:rgba(255,255,255,0.15);color:#fff;backdrop-filter:blur(10px);margin-bottom:20px;font-size:13px;padding:6px 14px;">
                    🌿 Platform Valuasi Ekonomi Terpercaya
                </div>
                <h1 style="font-size:52px;font-weight:900;color:#fff;line-height:1.15;margin-bottom:20px;">
                    Transparansi<br>
                    <span style="background:linear-gradient(90deg,#06d6a0,#3b82f6);-webkit-background-clip:text;-webkit-text-fill-color:transparent;">Valuasi Ekonomi</span>
                </h1>
                <p style="font-size:18px;color:rgba(255,255,255,0.8);line-height:1.7;margin-bottom:32px;max-width:500px;">
                    Mengukur dampak ekonomi dari proyek keberlanjutan dengan metodologi ilmiah — TCM, CVM, dan EOP.
                </p>
                <div style="display:flex;gap:12px;flex-wrap:wrap;">
                    <a href="{{ route('public.dashboard') }}" class="btn btn-secondary btn-lg">📊 Lihat Dashboard</a>
                    <a href="{{ route('public.glossary') }}" class="btn btn-outline-white btn-lg">Pelajari Metodologi</a>
                </div>
            </div>
            <div class="animate-fade-right">
                <div style="background:rgba(255,255,255,0.08);backdrop-filter:blur(20px);border:1px solid rgba(255,255,255,0.15);border-radius:20px;padding:32px;position:relative;">
                    <div style="position:absolute;top:-20px;right:-20px;width:80px;height:80px;background:linear-gradient(135deg,var(--secondary),var(--primary));border-radius:50%;opacity:0.3;filter:blur(30px);"></div>
                    <div style="text-align:center;margin-bottom:24px;">
                        <div style="font-size:14px;color:rgba(255,255,255,0.6);margin-bottom:8px;">Total Economic Value</div>
                        <div style="font-size:42px;font-weight:900;color:#fff;" data-count="{{ $totalTEV / 1000000000 }}" data-decimals="1" data-prefix="Rp " data-suffix=" T">Rp 0 T</div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                        <div style="background:rgba(255,255,255,0.08);border-radius:12px;padding:16px;text-align:center;">
                            <div style="font-size:28px;font-weight:800;color:#06d6a0;" data-count="{{ $totalBenefits / 1000000000 }}" data-decimals="1" data-prefix="Rp " data-suffix="T">0</div>
                            <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:4px;">Total Manfaat</div>
                        </div>
                        <div style="background:rgba(255,255,255,0.08);border-radius:12px;padding:16px;text-align:center;">
                            <div style="font-size:28px;font-weight:800;color:#818cf8;" data-count="{{ $avgBCR }}" data-decimals="2">0</div>
                            <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:4px;">Rata-rata BCR</div>
                        </div>
                    </div>
                    <div style="margin-top:16px;background:rgba(255,255,255,0.08);border-radius:12px;padding:16px;text-align:center;">
                        <div style="font-size:28px;font-weight:800;color:#f472b6;" data-count="{{ $publishedProjects }}" data-decimals="0">0</div>
                        <div style="font-size:12px;color:rgba(255,255,255,0.6);margin-top:4px;">Proyek Dipublikasikan</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Map Section --}}
<section style="padding:80px 0;background:var(--surface);" class="reveal">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;">
        <div style="text-align:center;margin-bottom:40px;">
            <div class="badge badge-primary" style="margin-bottom:12px;">📍 Lokasi Proyek</div>
            <h2 style="font-size:36px;font-weight:800;margin-bottom:12px;">Peta Valuasi Interaktif</h2>
            <p style="color:var(--text-secondary);font-size:16px;max-width:600px;margin:0 auto;">Visualisasi lokasi geografis dari semua proyek valuasi ekonomi yang telah dipublikasikan.</p>
        </div>
        <div style="border-radius:var(--radius-lg);overflow:hidden;box-shadow:var(--shadow-xl);border:1px solid var(--border);">
            <div id="valuation-map" style="height:450px;width:100%;"></div>
        </div>
    </div>
</section>

{{-- Features --}}
<section style="padding:80px 0;background:var(--surface-alt);" class="reveal">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;">
        <div style="text-align:center;margin-bottom:48px;">
            <h2 style="font-size:36px;font-weight:800;margin-bottom:12px;">Fitur Utama</h2>
            <p style="color:var(--text-secondary);font-size:16px;">Sistem lengkap untuk transparansi dan akuntabilitas data valuasi</p>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:24px;" class="stagger">
            @php $features = [
                ['icon' => '📊', 'title' => 'Dashboard Komprehensif', 'desc' => 'Lihat breakdown lengkap manfaat, biaya, dan metodologi valuasi dari setiap proyek.', 'color' => '#6366f1'],
                ['icon' => '📍', 'title' => 'Peta Valuasi Interaktif', 'desc' => 'Visualisasi lokasi geografis dari semua proyek valuasi dengan data real-time.', 'color' => '#06d6a0'],
                ['icon' => '📚', 'title' => 'Edukasi & Transparansi', 'desc' => 'Pelajari metodologi TCM, CVM, dan EOP yang digunakan untuk menghasilkan angka-angka tersebut.', 'color' => '#f72585'],
            ]; @endphp
            @foreach($features as $f)
            <div class="card" style="text-align:center;padding:36px 28px;">
                <div style="width:64px;height:64px;border-radius:16px;background:{{ $f['color'] }}15;display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 20px;">{{ $f['icon'] }}</div>
                <h3 style="font-size:18px;font-weight:700;margin-bottom:10px;">{{ $f['title'] }}</h3>
                <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;">{{ $f['desc'] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- CTA --}}
<section style="padding:80px 0;background:linear-gradient(135deg,#312e81,#4f46e5);position:relative;overflow:hidden;" class="reveal">
    <div style="position:absolute;top:0;right:0;width:400px;height:400px;background:radial-gradient(circle,rgba(6,214,160,0.2) 0%,transparent 70%);"></div>
    <div style="max-width:800px;margin:0 auto;padding:0 24px;text-align:center;position:relative;z-index:1;">
        <h2 style="font-size:36px;font-weight:800;color:#fff;margin-bottom:16px;">Siap Mengeksplorasi Data?</h2>
        <p style="font-size:18px;color:rgba(255,255,255,0.8);margin-bottom:32px;">Akses dashboard publik kami untuk melihat semua proyek valuasi ekonomi yang telah dipublikasikan.</p>
        <a href="{{ route('public.dashboard') }}" class="btn btn-secondary btn-lg">🔍 Buka Dashboard Publik</a>
    </div>
</section>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const map = L.map('valuation-map').setView([-2.5, 118], 5);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        const projects = @json($mapProjects ?? []);
        projects.forEach(p => {
            if (p.latitude && p.longitude) {
                const marker = L.marker([p.latitude, p.longitude]).addTo(map);
                marker.bindPopup(`<div style="font-family:Inter,sans-serif;"><strong style="font-size:14px;">${p.name}</strong><br><span style="font-size:12px;color:#64748b;">${p.location}</span><br><span style="font-size:13px;font-weight:600;color:#4f46e5;">TEV: Rp ${(p.tev/1000000000).toFixed(1)}T</span><br><a href="/project/${p.id}" style="font-size:12px;color:#06d6a0;">Lihat Detail →</a></div>`);
            }
        });
    });
</script>
@endsection
