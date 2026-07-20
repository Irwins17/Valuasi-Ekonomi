@extends('layouts.admin')
@section('page_title', 'Edit Proyek')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.projects.update', $project) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Kode Proyek</label>
                <input type="text" value="{{ $project->code }}" class="form-input" disabled style="background:var(--surface-alt);">
            </div>
            <div class="form-group">
                <label class="form-label">Nama Proyek</label>
                <input type="text" name="name" value="{{ old('name', $project->name) }}" required class="form-input">
                @error('name')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group">
                <label class="form-label">Deskripsi</label>
                <textarea name="description" class="form-input" rows="4">{{ old('description', $project->description) }}</textarea>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Lokasi</label>
                    <input type="text" name="location" value="{{ old('location', $project->location) }}" required class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-input">
                        @foreach(['draft','in_progress','completed','published'] as $s)
                        <option value="{{ $s }}" {{ $project->status === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label">Latitude</label>
                    <input type="number" name="latitude" value="{{ old('latitude', $project->latitude) }}" step="0.00001" class="form-input">
                </div>
                <div class="form-group">
                    <label class="form-label">Longitude</label>
                    <input type="number" name="longitude" value="{{ old('longitude', $project->longitude) }}" step="0.00001" class="form-input">
                </div>
            </div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('admin.projects.show', $project) }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
