@extends('layouts.admin')
@section('page_title', 'Harga Pasar')
@section('page_subtitle', 'Master data harga pasar komoditas')
@section('admin_content')
<div style="display:flex;justify-content:space-between;align-items:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
    {{-- Filter cakupan --}}
    <form method="GET" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        <label style="font-size:13px;color:var(--text-secondary);font-weight:500;">Cakupan:</label>
        <select name="scope" class="form-input" style="width:auto;padding:6px 30px 6px 10px;font-size:13px;" onchange="this.form.submit()">
            <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>Semua Proyek/Daerah</option>
            <option value="global" {{ $scope === 'global' ? 'selected' : '' }}>Umum (Global)</option>
            <optgroup label="Proyek Spesifik">
                @foreach($projects as $proj)
                <option value="{{ $proj->id }}" {{ (string) $scope === (string) $proj->id ? 'selected' : '' }}>{{ $proj->name }}</option>
                @endforeach
            </optgroup>
        </select>
        <noscript><button type="submit" class="btn btn-sm btn-outline">Filter</button></noscript>
        @if($scope !== 'all')
        <a href="{{ route('admin.master.prices.index') }}" class="btn btn-sm btn-ghost">Reset</a>
        @endif
    </form>
    <a href="{{ route('admin.master.prices.create') }}" class="btn btn-sm btn-primary">+ Tambah Harga</a>
</div>
<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Komoditas</th><th>Cakupan</th><th>Satuan</th><th style="text-align:right;">Harga (Rp)</th><th>Tahun</th><th>Sumber</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
            @forelse($prices as $p)
            <tr>
                <td style="font-weight:600;">{{ $p->commodity_name }}</td>
                <td>
                    @if($p->is_global)
                        <span class="badge badge-gray">Umum (Global)</span>
                    @else
                        <span class="badge badge-primary">{{ $p->project?->name ?? 'Proyek #'.$p->project_id }}</span>
                    @endif
                </td>
                <td>{{ $p->unit }}</td>
                <td style="text-align:right;font-weight:700;color:var(--primary);">Rp{{ number_format($p->price) }}</td>
                <td><span class="badge badge-info">{{ $p->year }}</span></td>
                <td style="font-size:13px;color:var(--text-secondary);">{{ $p->source ?? '-' }}</td>
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;">
                        <a href="{{ route('admin.master.prices.edit', $p) }}" class="btn btn-sm btn-ghost">Edit</a>
                        <form action="{{ route('admin.master.prices.destroy', $p) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">Belum ada data harga pasar untuk cakupan ini.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $prices->links() }}
</div>
@endsection
