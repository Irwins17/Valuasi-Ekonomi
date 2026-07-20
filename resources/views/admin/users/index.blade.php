@extends('layouts.admin')
@section('page_title', 'Manajemen Pengguna')
@section('admin_content')
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;" class="animate-fade-up">
    <div><p style="font-size:13px;color:var(--text-muted);">Kelola semua pengguna sistem</p></div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-sm btn-primary">+ Tambah Pengguna</a>
</div>
<div class="card">
    <div class="table-wrapper">
        <table class="data-table">
            <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Institusi</th><th>Status</th><th>Login Terakhir</th><th style="text-align:center;">Aksi</th></tr></thead>
            <tbody>
            @foreach($users as $user)
            <tr>
                <td style="font-weight:600;">{{ $user->name }}</td>
                <td style="font-size:13px;color:var(--text-secondary);">{{ $user->email }}</td>
                <td><span class="badge {{ $user->role?->slug === 'admin' ? 'badge-primary' : ($user->role?->slug === 'surveyor' ? 'badge-success' : 'badge-purple') }}">{{ $user->role?->name ?? '-' }}</span></td>
                <td style="font-size:13px;">{{ $user->institution ?? '-' }}</td>
                <td><span class="badge {{ $user->is_active ? 'badge-success' : 'badge-danger' }}">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
                <td style="font-size:12px;color:var(--text-muted);">{{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Belum pernah' }}</td>
                <td style="text-align:center;">
                    <div style="display:flex;gap:4px;justify-content:center;">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-ghost">Edit</a>
                        @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Hapus pengguna ini?')">@csrf @method('DELETE')<button class="btn btn-sm btn-ghost" style="color:var(--danger);">Hapus</button></form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    {{ $users->links() }}
</div>
@endsection
