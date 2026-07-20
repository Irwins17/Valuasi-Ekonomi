@extends('layouts.admin')
@section('page_title', 'Tambah Harga Pasar')
@section('admin_content')
<div style="max-width:600px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.master.prices.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.master.prices.store') }}">
            @csrf
            <div class="form-group">
                <label class="form-label">Cakupan Harga</label>
                <select name="project_id" class="form-input">
                    <option value="">Umum (Global) — berlaku untuk semua proyek/daerah</option>
                    <optgroup label="Spesifik untuk Proyek/Daerah">
                        @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>{{ $proj->name }} ({{ $proj->code }})</option>
                        @endforeach
                    </optgroup>
                </select>
                <p class="form-hint">Pilih "Umum" untuk harga acuan nasional, atau pilih proyek jika harga ini khusus daerah proyek tersebut.</p>
                @error('project_id')<p class="form-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-group"><label class="form-label">Nama Komoditas</label><input type="text" name="commodity_name" value="{{ old('commodity_name') }}" required class="form-input">@error('commodity_name')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Satuan</label><input type="text" name="unit" value="{{ old('unit') }}" required class="form-input" placeholder="Kg, Ton, m³"></div>
                <div class="form-group"><label class="form-label">Harga (Rp)</label><input type="text" name="price" value="{{ old('price') }}" required class="form-input rupiah-input" placeholder="50.000"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Tahun</label><input type="number" name="year" value="{{ old('year', date('Y')) }}" required class="form-input"></div>
                <div class="form-group"><label class="form-label">Sumber</label><input type="text" name="source" value="{{ old('source') }}" class="form-input" placeholder="BPS, Kementan, dll"></div>
            </div>
            <div class="form-group"><label class="form-label">Catatan</label><textarea name="notes" class="form-input" rows="2">{{ old('notes') }}</textarea></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.master.prices.index') }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
