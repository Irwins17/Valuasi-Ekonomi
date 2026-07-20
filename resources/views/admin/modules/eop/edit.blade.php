@extends('layouts.admin')
@section('page_title', 'Edit Data EOP')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.modules.eop.index', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.modules.eop.update', [$project, $eopData]) }}">
            @csrf @method('PUT')
            <div class="form-group"><label class="form-label">Nama Komoditas</label><input type="text" name="commodity_name" value="{{ old('commodity_name', $eopData->commodity_name) }}" required class="form-input"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Produksi Sebelum</label><input type="number" name="production_before" value="{{ old('production_before', $eopData->production_before) }}" required step="0.01" class="form-input"></div>
                <div class="form-group"><label class="form-label">Produksi Sesudah</label><input type="number" name="production_after" value="{{ old('production_after', $eopData->production_after) }}" required step="0.01" class="form-input"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Satuan</label><input type="text" name="unit" value="{{ old('unit', $eopData->unit) }}" required class="form-input"></div>
                <div class="form-group"><label class="form-label">Harga Pasar (Rp)</label><input type="text" name="market_price" value="{{ old('market_price', $eopData->market_price) }}" required class="form-input rupiah-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Tipe Dampak</label><select name="impact_type" class="form-input" required><option value="positive" {{ $eopData->impact_type === 'positive' ? 'selected' : '' }}>Positif</option><option value="negative" {{ $eopData->impact_type === 'negative' ? 'selected' : '' }}>Negatif</option></select></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.modules.eop.index', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
