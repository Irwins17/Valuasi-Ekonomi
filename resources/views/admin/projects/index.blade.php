@extends('layouts.admin')
@section('page_title', 'Manajemen Proyek')
@section('page_subtitle', 'Kelola semua proyek valuasi ekonomi')

@section('admin_content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;" class="animate-fade-up">
    <div></div>
    <a href="{{ route('admin.projects.create') }}" class="btn btn-sm btn-primary">+ Proyek Baru</a>
</div>

<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kode</th>
                    <th>Nama Proyek</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                    <th>TEV</th>
                    <th>BCR</th>
                    <th style="text-align:center;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $project)
                    <tr>
                        <td><span style="font-family:monospace;font-size:13px;color:var(--text-muted);">{{ $project->code }}</span></td>
                        <td>
                            <div>
                                <span style="font-weight:600;">{{ $project->name }}</span>
                                <p style="font-size:12px;color:var(--text-muted);margin-top:2px;">{{ Str::limit($project->description, 40) }}</p>
                            </div>
                        </td>
                        <td style="font-size:13px;color:var(--text-secondary);">{{ $project->location }}</td>
                        <td>
                            <span class="badge {{ $project->status === 'published' ? 'badge-success' : ($project->status === 'in_progress' ? 'badge-warning' : ($project->status === 'completed' ? 'badge-info' : 'badge-gray')) }}">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </td>
                        <td style="font-weight:700;color:var(--primary);">
                            @if($project->tev) Rp{{ number_format($project->tev / 1e9, 1) }}T @else - @endif
                        </td>
                        <td>
                            <span style="font-weight:700;color:{{ ($project->bcr ?? 0) >= 1 ? 'var(--success)' : 'var(--danger)' }};">
                                {{ $project->bcr ? number_format($project->bcr, 2) : '-' }}
                            </span>
                        </td>
                        <td style="text-align:center;">
                            <div style="display:flex;gap:4px;justify-content:center;">
                                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-sm btn-ghost">Lihat</a>
                                <a href="{{ route('admin.projects.edit', $project) }}" class="btn btn-sm btn-ghost">Edit</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align:center;color:var(--text-muted);padding:32px;">
                            <p style="margin-bottom:8px;">Belum ada proyek</p>
                            <a href="{{ route('admin.projects.create') }}" style="color:var(--primary);font-weight:600;text-decoration:none;">Buat proyek pertama →</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($projects->hasPages())
        <div class="pagination-wrapper">{{ $projects->links() }}</div>
    @endif
</div>
@endsection
