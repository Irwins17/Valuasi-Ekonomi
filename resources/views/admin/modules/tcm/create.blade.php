@extends('layouts.admin')
@section('page_title', 'Tambah Data TCM')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.modules.tcm.index', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Form Input TCM — {{ $project->name }}</h3>
        <form method="POST" action="{{ route('admin.modules.tcm.store', $project) }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">ID Responden (Nomor)</label><input type="number" name="respondent_id" value="{{ old('respondent_id') }}" required min="1" class="form-input" placeholder="1001">@error('respondent_id')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Asal Lokasi</label><input type="text" name="origin_location" value="{{ old('origin_location') }}" class="form-input" placeholder="Kota asal"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Jarak (km)</label><input type="number" name="distance" value="{{ old('distance') }}" required step="0.01" min="0" class="form-input">@error('distance')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Frekuensi Kunjungan</label><input type="number" name="visit_frequency" value="{{ old('visit_frequency') }}" required min="1" class="form-input">@error('visit_frequency')<p class="form-error">{{ $message }}</p>@enderror</div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Biaya Transportasi (Rp)</label><input type="text" name="transportation_cost" value="{{ old('transportation_cost') }}" required class="form-input rupiah-input" placeholder="50.000">@error('transportation_cost')<p class="form-error">{{ $message }}</p>@enderror</div>
                <div class="form-group"><label class="form-label">Biaya Waktu (Rp)</label><input type="text" name="time_cost" value="{{ old('time_cost') }}" required class="form-input rupiah-input" placeholder="10.000">@error('time_cost')<p class="form-error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="form-group"><label class="form-label">Kategori Responden</label><input type="text" name="respondent_category" value="{{ old('respondent_category') }}" class="form-input" placeholder="Wisatawan / Penduduk Lokal"></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.modules.tcm.index', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
