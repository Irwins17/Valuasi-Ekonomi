@extends('layouts.admin')
@section('page_title', 'Edit Pengguna')
@section('admin_content')
<div style="max-width:600px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.users.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf @method('PUT')
            <div class="form-group"><label class="form-label">Nama</label><input type="text" name="name" value="{{ old('name', $user->name) }}" required class="form-input"></div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email', $user->email) }}" required class="form-input"></div>
            <div class="form-group"><label class="form-label">Password Baru (kosongkan jika tidak diubah)</label><input type="password" name="password" class="form-input"></div>
            <div class="form-group"><label class="form-label">Role</label><select name="role_id" class="form-input" required>@foreach($roles as $role)<option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach</select></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" value="{{ old('phone', $user->phone) }}" class="form-input"></div>
                <div class="form-group"><label class="form-label">Institusi</label><input type="text" name="institution" value="{{ old('institution', $user->institution) }}" class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Status</label><select name="is_active" class="form-input"><option value="1" {{ $user->is_active ? 'selected' : '' }}>Aktif</option><option value="0" {{ !$user->is_active ? 'selected' : '' }}>Nonaktif</option></select></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
