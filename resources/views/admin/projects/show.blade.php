@extends('layouts.admin')
@section('page_title', $project->name)
@section('page_subtitle', 'Detail Proyek ' . $project->code)

@section('admin_content')
{{-- Actions --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;">
    <a href="{{ route('admin.projects.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;">← Kembali ke Daftar Proyek</a>
    <div style="display:flex;gap:8px;">
        <form action="{{ route('admin.projects.calculateTEV', $project) }}" method="POST" style="display:inline;">
            @csrf
            <button type="submit" class="btn btn-sm btn-secondary">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="4" y="2" width="16" height="20" rx="2"/><path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01"/></svg>
                Hitung TEV
            </button>
        </form>
        <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-outline">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z"/></svg>
            Edit
        </a>
    </div>
</div>

{{-- Stats --}}
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;">
    <div class="stat-card" style="text-align:center;">
        <div class="stat-label">TEV</div>
        <div class="stat-value" style="font-size:22px;color:var(--primary);">Rp{{ number_format(($project->tev ?? 0)/1e9, 2) }}T</div>
    </div>
    <div class="stat-card" style="text-align:center;">
        <div class="stat-label">Total Manfaat</div>
        <div class="stat-value" style="font-size:22px;color:var(--success);">Rp{{ number_format(($project->total_benefits ?? 0)/1e9, 2) }}T</div>
    </div>
    <div class="stat-card" style="text-align:center;">
        <div class="stat-label">Total Biaya</div>
        <div class="stat-value" style="font-size:22px;color:var(--danger);">Rp{{ number_format(($project->total_costs ?? 0)/1e9, 2) }}T</div>
    </div>
    <div class="stat-card" style="text-align:center;">
        <div class="stat-label">BCR</div>
        <div class="stat-value" style="font-size:22px;color:{{ ($project->bcr ?? 0) >= 1 ? 'var(--success)' : 'var(--danger)' }};">{{ number_format($project->bcr ?? 0, 4) }}</div>
    </div>
</div>

{{-- Module Links --}}
<div class="card" style="margin-bottom:24px;">
    <h3 style="font-size:15px;font-weight:700;margin-bottom:16px;">Modul Data Entry</h3>
    <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;">
        <a href="{{ route('admin.modules.eop.index', $project) }}" class="card module-tile">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22v-9"/><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6"/><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2"/><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2"/></svg>
            <div style="font-weight:700;color:var(--text);margin-top:8px;">EOP</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $project->eopData->count() }} records</div>
        </a>
        <a href="{{ route('admin.modules.tcm.index', $project) }}" class="card module-tile">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 17h14M5 17a2 2 0 01-2-2v-2.5L5 8h14l2 4.5V15a2 2 0 01-2 2M5 17v2a1 1 0 001 1h1a1 1 0 001-1v-2m8 0v2a1 1 0 001 1h1a1 1 0 001-1v-2"/><circle cx="7.5" cy="14.5" r="1"/><circle cx="16.5" cy="14.5" r="1"/></svg>
            <div style="font-weight:700;color:var(--text);margin-top:8px;">TCM</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $project->tcmData->count() }} records</div>
        </a>
        <a href="{{ route('admin.modules.cvm.index', $project) }}" class="card module-tile">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8"/><path d="M16 17H8"/><path d="M10 9H8"/></svg>
            <div style="font-weight:700;color:var(--text);margin-top:8px;">CVM</div>
            <div style="font-size:12px;color:var(--text-muted);">{{ $project->cvmData->count() }} records</div>
        </a>
    </div>
</div>
@section('styles')
<style>
    .module-tile { text-decoration: none; text-align: center; padding: 20px; border: 1px solid var(--border); color: var(--text-secondary); }
    .module-tile:hover { border-color: var(--primary); color: var(--primary); box-shadow: none; }
</style>
@endsection

{{-- Benefits Table --}}
<div class="card" style="margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="font-size:15px;font-weight:700;">Manfaat (Benefits)</h3>
        <a href="{{ route('admin.benefits.create', $project) }}" class="btn btn-sm btn-primary">+ Tambah</a>
    </div>
    @if($benefits->count())
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Kategori</th><th>Sub</th><th>Deskripsi</th><th>Metode</th><th style="text-align:right;">Nilai</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($benefits as $b)
                <tr>
                    <td><span class="badge {{ $b->category === 'direct_use' ? 'badge-primary' : ($b->category === 'indirect_use' ? 'badge-success' : 'badge-purple') }}">{{ str_replace('_',' ',ucfirst($b->category)) }}</span></td>
                    <td style="font-size:13px;">{{ str_replace('_',' ',ucfirst($b->subcategory)) }}</td>
                    <td style="font-size:13px;">{{ Str::limit($b->description, 40) }}</td>
                    <td><span class="badge badge-info">{{ $b->method_used }}</span></td>
                    <td style="text-align:right;font-weight:700;">Rp{{ number_format($b->value) }}</td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.benefits.edit', [$project, $b]) }}" class="btn btn-sm btn-ghost">Edit</a>
                            <form action="{{ route('admin.benefits.destroy', [$project, $b]) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $benefits->links() }}
    @else
    <p style="text-align:center;color:var(--text-muted);padding:20px;">Belum ada data manfaat.</p>
    @endif
</div>

{{-- Costs Table --}}
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <h3 style="font-size:15px;font-weight:700;">Biaya (Costs)</h3>
        <a href="{{ route('admin.costs.create', $project) }}" class="btn btn-sm btn-primary">+ Tambah</a>
    </div>
    @if($costs->count())
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Kategori</th><th>Sub</th><th>Deskripsi</th><th>Tipe</th><th style="text-align:right;">Nilai</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($costs as $c)
                <tr>
                    <td><span class="badge {{ $c->category === 'direct_cost' ? 'badge-warning' : 'badge-danger' }}">{{ str_replace('_',' ',ucfirst($c->category)) }}</span></td>
                    <td style="font-size:13px;">{{ str_replace('_',' ',ucfirst($c->subcategory)) }}</td>
                    <td style="font-size:13px;">{{ Str::limit($c->description, 40) }}</td>
                    <td style="font-size:13px;">{{ $c->payment_type ?? '-' }}</td>
                    <td style="text-align:right;font-weight:700;">Rp{{ number_format($c->value) }}</td>
                    <td>
                        <div style="display:flex;gap:4px;">
                            <a href="{{ route('admin.costs.edit', [$project, $c]) }}" class="btn btn-sm btn-ghost">Edit</a>
                            <form action="{{ route('admin.costs.destroy', [$project, $c]) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $costs->links() }}
    @else
    <p style="text-align:center;color:var(--text-muted);padding:20px;">Belum ada data biaya.</p>
    @endif
</div>
@endsection
