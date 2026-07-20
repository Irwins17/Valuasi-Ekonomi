@extends('layouts.admin')
@section('page_title', 'Data EOP — ' . $project->name)
@section('admin_content')
<a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali ke Proyek</a>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div><h2 style="font-size:18px;font-weight:700;">Effect on Production (EOP)</h2><p style="font-size:13px;color:var(--text-muted);">Data perubahan volume produksi komoditas</p></div>
    <a href="{{ route('admin.modules.eop.create', $project) }}" class="btn btn-sm btn-primary">+ Tambah Data</a>
</div>
<div class="card">
    @if($eopData->count())
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Komoditas</th><th>Produksi Sebelum</th><th>Produksi Sesudah</th><th>Δ Produksi</th><th>Harga Pasar</th><th style="text-align:right;">Total Nilai</th><th>Dampak</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($eopData as $d)
            <tr>
                <td style="font-weight:600;">{{ $d->commodity_name }}</td>
                <td>{{ number_format($d->production_before,0) }} {{ $d->unit }}</td>
                <td>{{ number_format($d->production_after,0) }} {{ $d->unit }}</td>
                <td style="font-weight:600;color:{{ $d->production_change >= 0 ? 'var(--success)' : 'var(--danger)' }}">{{ $d->production_change >= 0 ? '+' : '' }}{{ number_format($d->production_change,0) }}</td>
                <td>Rp{{ number_format($d->market_price) }}</td>
                <td style="text-align:right;font-weight:700;color:var(--primary);">Rp{{ number_format($d->total_value) }}</td>
                <td><span class="badge {{ $d->impact_type === 'positive' ? 'badge-success' : 'badge-danger' }}">{{ ucfirst($d->impact_type) }}</span></td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('admin.modules.eop.edit', [$project, $d]) }}" class="btn btn-sm btn-ghost">Edit</a>
                        <form action="{{ route('admin.modules.eop.destroy', [$project, $d]) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $eopData->links() }}
    @else
    <p style="text-align:center;color:var(--text-muted);padding:32px;">Belum ada data EOP. <a href="{{ route('admin.modules.eop.create', $project) }}">Tambah data pertama →</a></p>
    @endif
</div>
@endsection
