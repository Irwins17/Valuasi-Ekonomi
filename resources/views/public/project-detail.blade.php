@extends('layouts.guest')
@section('title', $project->name . ' — Valuasi Ekonomi')
@section('guest_content')

<section style="background:linear-gradient(135deg,#312e81,#4f46e5);padding:60px 0;">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;" class="animate-fade-up">
        <a href="{{ route('public.dashboard') }}" style="color:rgba(255,255,255,0.6);font-size:13px;text-decoration:none;display:flex;align-items:center;gap:6px;margin-bottom:16px;">
            ← Kembali ke Dashboard
        </a>
        <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
                <span style="font-family:monospace;font-size:13px;color:rgba(255,255,255,0.5);">{{ $project->code }}</span>
                <h1 style="font-size:36px;font-weight:800;color:#fff;margin-top:4px;">{{ $project->name }}</h1>
                <div style="display:flex;align-items:center;gap:16px;margin-top:12px;">
                    <span style="color:rgba(255,255,255,0.7);font-size:14px;display:flex;align-items:center;gap:6px;">📍 {{ $project->location }}</span>
                    <span class="badge badge-success">{{ ucfirst($project->status) }}</span>
                </div>
            </div>
        </div>
    </div>
</section>

<section style="padding:48px 0;">
    <div style="max-width:1280px;margin:0 auto;padding:0 24px;">
        {{-- Stats --}}
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:40px;" class="stagger">
            <div class="stat-card" style="text-align:center;">
                <div class="stat-label">Total Economic Value</div>
                <div class="stat-value" style="color:var(--primary);">Rp{{ number_format(($project->tev ?? 0)/1e9, 1) }}T</div>
            </div>
            <div class="stat-card" style="text-align:center;">
                <div class="stat-label">Total Manfaat</div>
                <div class="stat-value" style="color:var(--secondary);">Rp{{ number_format(($project->total_benefits ?? 0)/1e9, 1) }}T</div>
            </div>
            <div class="stat-card" style="text-align:center;">
                <div class="stat-label">Total Biaya</div>
                <div class="stat-value" style="color:var(--accent);">Rp{{ number_format(($project->total_costs ?? 0)/1e9, 1) }}T</div>
            </div>
            <div class="stat-card" style="text-align:center;">
                <div class="stat-label">BCR</div>
                <div class="stat-value" style="color:{{ ($project->bcr ?? 0) >= 1 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($project->bcr ?? 0, 2) }}</div>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px;">
            <div>
                {{-- Benefits --}}
                <div class="card" style="margin-bottom:24px;">
                    <h2 style="font-size:18px;font-weight:700;margin-bottom:20px;">Komponen Manfaat (Benefits)</h2>
                    @if($benefits->count())
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead><tr><th>Kategori</th><th>Subkategori</th><th>Deskripsi</th><th>Metode</th><th style="text-align:right;">Nilai</th></tr></thead>
                            <tbody>
                            @foreach($benefits as $b)
                                <tr>
                                    <td><span class="badge {{ $b->category === 'direct_use' ? 'badge-primary' : ($b->category === 'indirect_use' ? 'badge-success' : 'badge-purple') }}">{{ str_replace('_',' ',ucfirst($b->category)) }}</span></td>
                                    <td style="font-size:13px;">{{ str_replace('_',' ',ucfirst($b->subcategory)) }}</td>
                                    <td style="font-size:13px;">{{ $b->description }}</td>
                                    <td><span class="badge badge-info">{{ $b->method_used }}</span></td>
                                    <td style="text-align:right;font-weight:700;color:var(--primary);">Rp{{ number_format($b->value) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p style="text-align:center;color:var(--text-muted);padding:24px;">Belum ada data manfaat.</p>
                    @endif
                </div>

                {{-- Costs --}}
                <div class="card">
                    <h2 style="font-size:18px;font-weight:700;margin-bottom:20px;">Komponen Biaya (Costs)</h2>
                    @if($costs->count())
                    <div class="table-wrapper">
                        <table class="data-table">
                            <thead><tr><th>Kategori</th><th>Subkategori</th><th>Deskripsi</th><th style="text-align:right;">Nilai</th></tr></thead>
                            <tbody>
                            @foreach($costs as $c)
                                <tr>
                                    <td><span class="badge {{ $c->category === 'direct_cost' ? 'badge-warning' : 'badge-danger' }}">{{ str_replace('_',' ',ucfirst($c->category)) }}</span></td>
                                    <td style="font-size:13px;">{{ str_replace('_',' ',ucfirst($c->subcategory)) }}</td>
                                    <td style="font-size:13px;">{{ $c->description }}</td>
                                    <td style="text-align:right;font-weight:700;color:var(--accent);">Rp{{ number_format($c->value) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <p style="text-align:center;color:var(--text-muted);padding:24px;">Belum ada data biaya.</p>
                    @endif
                </div>
            </div>

            <div>
                {{-- Description --}}
                <div class="card" style="margin-bottom:24px;">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:12px;">Deskripsi Proyek</h3>
                    <p style="font-size:14px;color:var(--text-secondary);line-height:1.7;">{{ $project->description ?: 'Tidak ada deskripsi.' }}</p>
                </div>

                {{-- Benefit Chart --}}
                <div class="card" style="margin-bottom:24px;">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Komposisi Manfaat</h3>
                    <canvas id="projectBenefitChart" style="max-height:200px;"></canvas>
                </div>

                {{-- Map --}}
                @if($project->latitude && $project->longitude)
                <div class="card">
                    <h3 style="font-size:15px;font-weight:700;margin-bottom:12px;">Lokasi</h3>
                    <div id="project-map" style="height:200px;border-radius:var(--radius-sm);overflow:hidden;"></div>
                </div>
                @endif
            </div>
        </div>
    </div>
</section>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Benefit Chart
    const benData = @json($benefits->groupBy('category')->map->sum('value'));
    new Chart(document.getElementById('projectBenefitChart'), {
        type: 'doughnut',
        data: {
            labels: Object.keys(benData).map(k => k.replace('_',' ')),
            datasets: [{ data: Object.values(benData), backgroundColor: ['#6366f1','#06d6a0','#f72585'], borderWidth: 0, borderRadius: 3 }]
        },
        options: { responsive: true, cutout: '60%', plugins: { legend: { position: 'bottom', labels: { font: { family: 'Inter', size: 11 }, padding: 12 } } } }
    });

    // Map
    @if($project->latitude && $project->longitude)
    const map = L.map('project-map').setView([{{ $project->latitude }}, {{ $project->longitude }}], 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OSM' }).addTo(map);
    L.marker([{{ $project->latitude }}, {{ $project->longitude }}]).addTo(map).bindPopup('{{ $project->name }}').openPopup();
    @endif
});
</script>
@endsection
