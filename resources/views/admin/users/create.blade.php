@extends('layouts.admin')
@section('page_title', 'Tambah Pengguna')
@section('admin_content')
<div style="max-width:600px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.users.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            <div class="form-group"><label class="form-label">Nama</label><input type="text" name="name" value="{{ old('name') }}" required class="form-input">@error('name')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div class="form-group"><label class="form-label">Email</label><input type="email" name="email" value="{{ old('email') }}" required class="form-input">@error('email')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Password</label><input type="password" name="password" required class="form-input">@error('password')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Konfirmasi Password</label><input type="password" name="password_confirmation" required class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Role</label><select name="role_id" class="form-input" required>@foreach($roles as $role)<option value="{{ $role->id }}">{{ $role->name }}</option>@endforeach</select></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Telepon</label><input type="text" name="phone" value="{{ old('phone') }}" class="form-input"></div>
                <div class="form-group"><label class="form-label">Institusi</label><input type="text" name="institution" value="{{ old('institution') }}" class="form-input"></div>
            </div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
