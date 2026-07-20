@extends('layouts.admin')
@section('page_title', 'Data TCM — ' . $project->name)
@section('admin_content')
<a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali ke Proyek</a>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div><h2 style="font-size:18px;font-weight:700;">Travel Cost Method (TCM)</h2><p style="font-size:13px;color:var(--text-muted);">Data biaya perjalanan pengunjung</p></div>
    <a href="{{ route('admin.modules.tcm.create', $project) }}" class="btn btn-sm btn-primary">+ Tambah Data</a>
</div>
@if(isset($stats))
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;" class="stagger">
    <div class="stat-card"><div class="stat-label">Responden</div><div class="stat-value" style="font-size:20px;">{{ $stats['total_respondents'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Rata-rata Jarak</div><div class="stat-value" style="font-size:20px;">{{ number_format($stats['avg_distance'] ?? 0, 1) }} km</div></div>
    <div class="stat-card"><div class="stat-label">Rata-rata Surplus</div><div class="stat-value" style="font-size:20px;color:var(--primary);">Rp{{ number_format($stats['avg_surplus'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label">Total Surplus</div><div class="stat-value" style="font-size:20px;color:var(--success);">Rp{{ number_format($stats['total_surplus'] ?? 0) }}</div></div>
</div>
@endif
<div class="card">
    @if($tcmData->count())
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>ID Responden</th><th>Asal</th><th>Jarak</th><th>Biaya Transport</th><th>Biaya Waktu</th><th>Frekuensi</th><th style="text-align:right;">Surplus</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($tcmData as $d)
            <tr>
                <td style="font-family:monospace;font-size:13px;">{{ $d->respondent_id }}</td>
                <td style="font-size:13px;">{{ $d->origin_location ?? '-' }}</td>
                <td>{{ number_format($d->distance,1) }} km</td>
                <td>Rp{{ number_format($d->transportation_cost) }}</td>
                <td>Rp{{ number_format($d->time_cost) }}</td>
                <td style="text-align:center;">{{ $d->visit_frequency }}x</td>
                <td style="text-align:right;font-weight:700;color:var(--primary);">Rp{{ number_format($d->consumer_surplus) }}</td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('admin.modules.tcm.edit', [$project, $d]) }}" class="btn btn-sm btn-ghost">Edit</a>
                        <form action="{{ route('admin.modules.tcm.destroy', [$project, $d]) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $tcmData->links() }}
    @else
    <p style="text-align:center;color:var(--text-muted);padding:32px;">Belum ada data TCM. <a href="{{ route('admin.modules.tcm.create', $project) }}">Tambah data pertama →</a></p>
    @endif
</div>
@endsection
