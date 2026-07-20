@extends('layouts.admin')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Ringkasan data dan aktivitas terkini')

@section('admin_content')
{{-- Stat Cards --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:24px;">
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
                <div class="stat-label">Total Proyek</div>
                <div class="stat-value">{{ $totalProjects }}</div>
            </div>
            <div class="stat-icon" style="background:var(--surface-alt);color:var(--text-secondary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
                <div class="stat-label">Dipublikasikan</div>
                <div class="stat-value" style="color:var(--success);">{{ $publishedProjects }}</div>
            </div>
            <div class="stat-icon" style="background:var(--surface-alt);color:var(--success);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><path d="M22 4L12 14.01l-3-3"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
                <div class="stat-label">Dalam Proses</div>
                <div class="stat-value" style="color:var(--warning);">{{ $inProgressProjects }}</div>
            </div>
            <div class="stat-icon" style="background:var(--surface-alt);color:var(--warning);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div style="display:flex;justify-content:space-between;align-items:start;">
            <div>
                <div class="stat-label">Total TEV</div>
                <div class="stat-value" style="color:var(--primary);">Rp{{ number_format(($aggregatedTEV ?? 0)/1e9, 1) }}T</div>
            </div>
            <div class="stat-icon" style="background:var(--surface-alt);color:var(--primary);">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 1v22"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
        </div>
    </div>
</div>

{{-- Data Collection --}}
<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-bottom:24px;">
    <div class="card" style="display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:var(--surface-alt);display:flex;align-items:center;justify-content:center;color:var(--text-secondary);flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22v-9"/><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6"/><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2"/><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2"/></svg>
        </div>
        <div><div style="font-size:22px;font-weight:700;">{{ $totalEOPRecords }}</div><div style="font-size:13px;color:var(--text-muted);">Data EOP</div></div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:var(--surface-alt);display:flex;align-items:center;justify-content:center;color:var(--text-secondary);flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17h14M5 17a2 2 0 01-2-2v-2.5L5 8h14l2 4.5V15a2 2 0 01-2 2M5 17v2a1 1 0 001 1h1a1 1 0 001-1v-2m8 0v2a1 1 0 001 1h1a1 1 0 001-1v-2"/><circle cx="7.5" cy="14.5" r="1"/><circle cx="16.5" cy="14.5" r="1"/></svg>
        </div>
        <div><div style="font-size:22px;font-weight:700;">{{ $totalTCMRecords }}</div><div style="font-size:13px;color:var(--text-muted);">Data TCM</div></div>
    </div>
    <div class="card" style="display:flex;align-items:center;gap:16px;">
        <div style="width:44px;height:44px;border-radius:10px;background:var(--surface-alt);display:flex;align-items:center;justify-content:center;color:var(--text-secondary);flex-shrink:0;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/></svg>
        </div>
        <div><div style="font-size:22px;font-weight:700;">{{ $totalCVMRecords }}</div><div style="font-size:13px;color:var(--text-muted);">Data CVM</div></div>
    </div>
</div>

{{-- Charts Row --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:20px;margin-bottom:28px;">
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Tren Proyek Bulanan</h3>
        <div style="height:240px;"><canvas id="monthlyChart"></canvas></div>
    </div>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Ringkasan</h3>
        <div style="display:flex;flex-direction:column;gap:12px;">
            <div style="padding:14px;background:var(--primary-50);border-radius:var(--radius-sm);">
                <div style="font-size:12px;color:var(--text-muted);">TEV Agregat</div>
                <div style="font-size:20px;font-weight:800;color:var(--primary);">Rp{{ number_format(($aggregatedTEV ?? 0)/1e9, 1) }}T</div>
            </div>
            <div style="padding:14px;background:#d1fae5;border-radius:var(--radius-sm);">
                <div style="font-size:12px;color:var(--text-muted);">Pengguna Aktif</div>
                <div style="font-size:20px;font-weight:800;color:var(--success);">{{ $totalUsers }}</div>
            </div>
            <div style="padding:14px;background:#fef3c7;border-radius:var(--radius-sm);">
                <div style="font-size:12px;color:var(--text-muted);">Surveyor</div>
                <div style="font-size:20px;font-weight:800;color:#d97706;">{{ $totalSurveyors }}</div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Projects --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
        <h3 style="font-size:16px;font-weight:700;">Proyek Terbaru</h3>
        <a href="{{ route('admin.projects.index') }}" class="btn btn-sm btn-ghost">Lihat Semua →</a>
    </div>
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th>Status</th><th>TEV</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
            @foreach($lastProjects as $project)
                <tr>
                    <td><span style="font-family:monospace;font-size:13px;color:var(--text-muted);">{{ $project->code }}</span></td>
                    <td style="font-weight:600;">{{ $project->name }}</td>
                    <td style="font-size:13px;color:var(--text-secondary);">{{ $project->location }}</td>
                    <td>
                        <span class="badge {{ $project->status === 'published' ? 'badge-success' : ($project->status === 'in_progress' ? 'badge-warning' : ($project->status === 'completed' ? 'badge-info' : 'badge-gray')) }}">
                            {{ ucfirst(str_replace('_',' ',$project->status)) }}
                        </span>
                    </td>
                    <td style="font-weight:700;color:var(--primary);">{{ $project->tev ? 'Rp'.number_format($project->tev/1e9,1).'T' : '-' }}</td>
                    <td style="text-align:center;"><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-ghost">Lihat</a></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const stats = @json($monthlyStats);
    new Chart(document.getElementById('monthlyChart'), {
        type: 'bar',
        data: {
            labels: Object.keys(stats),
            datasets: [{
                data: Object.values(stats),
                backgroundColor: '#6366f1',
                borderRadius: 6,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { stepSize: 1, font: { family: 'Inter' } } },
                x: { grid: { display: false }, ticks: { font: { family: 'Inter', size: 11 } } }
            }
        }
    });
});
</script>
@endsection
