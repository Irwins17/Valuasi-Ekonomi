@extends('layouts.admin')
@section('page_title', 'Tambah Cost')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Tambah Biaya — {{ $project->name }}</h3>
        <form method="POST" action="{{ route('admin.costs.store', $project) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Kategori</label><select name="category" class="form-input" required><option value="direct_cost">Direct Cost</option><option value="indirect_cost">Indirect Cost</option></select></div>
                <div class="form-group"><label class="form-label">Subkategori</label><select name="subcategory" class="form-input" required><option value="investment">Investasi</option><option value="operation_maintenance">Operasi & Pemeliharaan</option><option value="opportunity_cost">Opportunity Cost</option><option value="externality">Eksternalitas</option><option value="other">Lainnya</option></select></div>
            </div>
            <div class="form-group"><label class="form-label">Deskripsi</label><input type="text" name="description" value="{{ old('description') }}" required class="form-input">@error('description')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Nilai (Rp)</label><input type="text" name="value" value="{{ old('value') }}" required class="form-input rupiah-input" placeholder="50.000">@error('value')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Tipe Pembayaran</label><input type="text" name="payment_type" value="{{ old('payment_type') }}" class="form-input" placeholder="Investasi Awal / Tahunan"></div>
            </div>
            <div class="form-group"><label class="form-label">Catatan</label><textarea name="calculation_notes" class="form-input" rows="3">{{ old('calculation_notes') }}</textarea></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
