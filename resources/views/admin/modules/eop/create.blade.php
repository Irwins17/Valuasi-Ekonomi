@extends('layouts.admin')
@section('page_title', 'Tambah Data EOP')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.modules.eop.index', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Form Input EOP — {{ $project->name }}</h3>
        <form method="POST" action="{{ route('admin.modules.eop.store', $project) }}">
            @csrf
            <div class="form-group"><label class="form-label">Nama Komoditas</label><input type="text" name="commodity_name" value="{{ old('commodity_name') }}" required class="form-input" placeholder="Contoh: Padi, Ikan, Kayu">@error('commodity_name')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Produksi Sebelum</label><input type="number" name="production_before" value="{{ old('production_before') }}" required step="0.01" class="form-input" placeholder="0.00">@error('production_before')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Produksi Sesudah</label><input type="number" name="production_after" value="{{ old('production_after') }}" required step="0.01" class="form-input" placeholder="0.00">@error('production_after')<p class="form-error">{{ $message }}</p>@enderror</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Satuan</label><input type="text" name="unit" value="{{ old('unit') }}" required class="form-input" placeholder="Ton, Kg, m³">@error('unit')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Harga Pasar (Rp)</label><input type="text" name="market_price" value="{{ old('market_price') }}" required class="form-input rupiah-input" placeholder="50.000">@error('market_price')<p class="form-error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="form-group"><label class="form-label">Tipe Dampak</label><select name="impact_type" class="form-input" required><option value="positive" {{ old('impact_type') === 'positive' ? 'selected' : '' }}>Positif (Keuntungan)</option><option value="negative" {{ old('impact_type') === 'negative' ? 'selected' : '' }}>Negatif (Kerugian)</option></select></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);">
                <button type="submit" class="btn btn-primary">Simpan Data</button>
                <a href="{{ route('admin.modules.eop.index', $project) }}" class="btn btn-ghost">Batal</a>
            </div>
        </form>
    </div>
</div>
@endsection
