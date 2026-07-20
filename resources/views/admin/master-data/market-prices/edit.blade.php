@extends('layouts.admin')
@section('page_title', 'Edit Harga Pasar')
@section('admin_content')
<div style="max-width:600px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.master.prices.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.master.prices.update', $price) }}">
            @csrf @method('PUT')
            <div class="form-group">
                <label class="form-label">Cakupan Harga</label>
                <select name="project_id" class="form-input">
                    <option value="">Umum (Global) — berlaku untuk semua proyek/daerah</option>
                    <optgroup label="Spesifik untuk Proyek/Daerah">
                        @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ (string) old('project_id', $price->project_id) === (string) $proj->id ? 'selected' : '' }}>{{ $proj->name }} ({{ $proj->code }})</option>
                        @endforeach
                    </optgroup>
                </select>
                @error('project_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group"><label class="form-label">Nama Komoditas</label><input type="text" name="commodity_name" value="{{ old('commodity_name', $price->commodity_name) }}" required class="form-input"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Satuan</label><input type="text" name="unit" value="{{ old('unit', $price->unit) }}" required class="form-input"></div>
                <div class="form-group"><label class="form-label">Harga (Rp)</label><input type="text" name="price" value="{{ old('price', $price->price) }}" required class="form-input rupiah-input"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Tahun</label><input type="number" name="year" value="{{ old('year', $price->year) }}" required class="form-input"></div>
                <div class="form-group"><label class="form-label">Sumber</label><input type="text" name="source" value="{{ old('source', $price->source) }}" class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Catatan</label><textarea name="notes" class="form-input" rows="2">{{ old('notes', $price->notes) }}</textarea></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.master.prices.index') }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
