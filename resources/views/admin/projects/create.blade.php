@extends('layouts.admin')
@section('page_title', 'Buat Proyek Baru')

@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.projects.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali ke Daftar Proyek</a>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:24px;">Form Proyek Baru</h3>
        <form method="POST" action="{{ route('admin.projects.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Kode Proyek</label>
                <input type="text" name="code" value="{{ old('code') }}" required class="form-input" placeholder="PROJ-001">
                @error('code')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Nama Proyek</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="form-input" placeholder="Nama proyek valuasi">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-input" rows="4" placeholder="Deskripsi proyek...">{{ old('description') }}</textarea>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location') }}" required class="form-input" placeholder="Lokasi geografis">
                    @error('location')<p class="form-error">{{ $message }}</p>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="number" name="latitude" value="{{ old('latitude') }}" step="0.00001" class="form-input" placeholder="-6.200000">
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="number" name="longitude" value="{{ old('longitude') }}" step="0.00001" class="form-input" placeholder="106.816666">
                </div>
            </div>

            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);">
                <button type="submit" class="btn btn-primary">Buat Proyek</button>
                <a href="{{ route('admin.projects.index') }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
