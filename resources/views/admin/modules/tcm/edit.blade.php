@extends('layouts.admin')
@section('page_title', 'Edit Data TCM')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.modules.tcm.index', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.modules.tcm.update', [$project, $tcmData]) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Jarak (km)</label><input type="number" name="distance" value="{{ old('distance', $tcmData->distance) }}" required step="0.01" class="form-input"></div>
                <div class="form-group"><label class="form-label">Frekuensi Kunjungan</label><input type="number" name="visit_frequency" value="{{ old('visit_frequency', $tcmData->visit_frequency) }}" required min="1" class="form-input"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Biaya Transportasi (Rp)</label><input type="text" name="transportation_cost" value="{{ old('transportation_cost', $tcmData->transportation_cost) }}" required class="form-input rupiah-input"></div>
                <div class="form-group"><label class="form-label">Biaya Waktu (Rp)</label><input type="text" name="time_cost" value="{{ old('time_cost', $tcmData->time_cost) }}" required class="form-input rupiah-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Asal Lokasi</label><input type="text" name="origin_location" value="{{ old('origin_location', $tcmData->origin_location) }}" class="form-input"></div>
            <div class="form-group"><label class="form-label">Kategori</label><input type="text" name="respondent_category" value="{{ old('respondent_category', $tcmData->respondent_category) }}" class="form-input"></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.modules.tcm.index', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
