@extends('layouts.admin')
@section('page_title', 'Data CVM — ' . $project->name)
@section('admin_content')
<a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali ke Proyek</a>
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <div><h2 style="font-size:18px;font-weight:700;">Contingent Valuation Method (CVM)</h2><p style="font-size:13px;color:var(--text-muted);">Data Willingness to Pay (WTP) responden</p></div>
    <a href="{{ route('admin.modules.cvm.create', $project) }}" class="btn btn-sm btn-primary">+ Tambah Data</a>
</div>
@if(isset($stats))
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:12px;margin-bottom:20px;" class="stagger">
    <div class="stat-card"><div class="stat-label">Responden</div><div class="stat-value" style="font-size:20px;">{{ $stats['total_respondents'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Bersedia Bayar</div><div class="stat-value" style="font-size:20px;color:var(--success);">{{ $stats['willing_to_pay_count'] }}</div></div>
    <div class="stat-card"><div class="stat-label">Mean WTP</div><div class="stat-value" style="font-size:20px;color:var(--primary);">Rp{{ number_format($stats['mean_wtp'] ?? 0) }}</div></div>
    <div class="stat-card"><div class="stat-label">Median WTP</div><div class="stat-value" style="font-size:20px;color:var(--accent);">Rp{{ number_format($stats['median_wtp'] ?? 0) }}</div></div>
</div>
@endif
<div class="card">
    @if($cvmData->count())
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>ID Responden</th><th>WTP</th><th>Bersedia?</th><th>Pendapatan RT</th><th>Pendidikan</th><th>Lokasi</th><th>Aksi</th></tr></thead>
            <tbody>
            @foreach($cvmData as $d)
            <tr>
                <td style="font-family:monospace;font-size:13px;">{{ $d->respondent_id }}</td>
                <td style="font-weight:700;color:var(--primary);">Rp{{ number_format($d->wtp ?? 0) }}</td>
                <td><span class="badge {{ $d->willing_to_pay ? 'badge-success' : 'badge-danger' }}">{{ $d->willing_to_pay ? 'Ya' : 'Tidak' }}</span></td>
                <td style="font-size:13px;">{{ $d->household_income ? 'Rp'.number_format($d->household_income) : '-' }}</td>
                <td style="font-size:13px;">{{ $d->education_level ?? '-' }}</td>
                <td style="font-size:13px;">{{ $d->respondent_location ?? '-' }}</td>
                <td>
                    <div style="display:flex;gap:4px;">
                        <a href="{{ route('admin.modules.cvm.edit', [$project, $d]) }}" class="btn btn-sm btn-ghost">Edit</a>
                        <form action="{{ route('admin.modules.cvm.destroy', [$project, $d]) }}" method="POST" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $cvmData->links() }}
    @else
    <p style="text-align:center;color:var(--text-muted);padding:32px;">Belum ada data CVM. <a href="{{ route('admin.modules.cvm.create', $project) }}">Tambah data pertama →</a></p>
    @endif
</div>
@endsection
