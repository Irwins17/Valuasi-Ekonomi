@extends('layouts.admin')
@section('page_title', 'Tambah Benefit')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali ke Proyek</a>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Tambah Manfaat — {{ $project->name }}</h3>
        <form method="POST" action="{{ route('admin.benefits.store', $project) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Kategori</label><select name="category" class="form-input" required><option value="direct_use">Direct Use</option><option value="indirect_use">Indirect Use</option><option value="non_use">Non-Use</option></select>@error('category')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Subkategori</label><select name="subcategory" class="form-input" required><option value="production">Produksi</option><option value="tourism">Wisata</option><option value="recreation">Rekreasi</option><option value="water_regulation">Regulasi Air</option><option value="carbon_sequestration">Serapan Karbon</option><option value="existence_value">Nilai Keberadaan</option><option value="bequest_value">Nilai Warisan</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">Deskripsi</label><input type="text" name="description" value="{{ old('description') }}" required class="form-input" placeholder="Deskripsi manfaat">@error('description')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Nilai (Rp)</label><input type="text" name="value" value="{{ old('value') }}" required class="form-input rupiah-input" placeholder="50.000">@error('value')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Metode</label><select name="method_used" class="form-input"><option value="EOP">EOP</option><option value="TCM">TCM</option><option value="CVM">CVM</option><option value="RC">Replacement Cost</option><option value="Manual">Manual</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">Sumber Data</label><select name="data_source" class="form-input" required><option value="eop">EOP</option><option value="tcm">TCM</option><option value="cvm">CVM</option><option value="manual">Manual</option><option value="literature">Literatur</option></select></div>
            <div class="form-group"><label class="form-label">Catatan Perhitungan</label><textarea name="calculation_notes" class="form-input" rows="3">{{ old('calculation_notes') }}</textarea></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
