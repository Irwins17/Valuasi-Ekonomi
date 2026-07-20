@extends('layouts.admin')
@section('page_title', 'Koefisien Lingkungan')
@section('page_subtitle', 'Master data koefisien lingkungan')
@section('admin_content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div></div>
    <a href="{{ route('admin.master.coefficients.create') }}" class="btn btn-sm btn-primary">+ Tambah Koefisien</a>
</div>
<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Kode</th><th>Nama</th><th>Tipe</th><th style="text-align:right;">Nilai</th><th>Satuan</th><th>Sumber</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
            @forelse($coefficients as $c)
            <tr>
                <td style="font-family:monospace;font-size:13px;">{{ $c->code }}</td>
                <td style="font-weight:600;">{{ $c->name }}</td>
                <td><span class="badge badge-info">{{ $c->type }}</span></td>
                <td style="text-align:right;font-weight:700;">{{ number_format($c->value, 4) }}</td>
                <td style="font-size:13px;">{{ $c->unit }}</td>
                <td style="font-size:13px;color:var(--text-secondary);">{{ $c->source ?? '-' }}</td>
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;">
                        <a href="{{ route('admin.master.coefficients.edit', $c) }}" class="btn btn-sm btn-ghost">Edit</a>
                        <form action="{{ route('admin.master.coefficients.destroy', $c) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">Belum ada data koefisien.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $coefficients->links() }}
</div>
@endsection
