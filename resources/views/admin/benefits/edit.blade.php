@extends('layouts.admin')
@section('page_title', 'Edit Benefit')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.benefits.update', [$project, $benefit]) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Kategori</label><select name="category" class="form-input" required>@foreach(['direct_use'=>'Direct Use','indirect_use'=>'Indirect Use','non_use'=>'Non-Use'] as $v=>$l)<option value="{{ $v }}" {{ $benefit->category === $v ? 'selected' : '' }}>{{ $l }}</option>@endforeach</select></div>
                <div class="form-group"><label class="form-label">Subkategori</label><select name="subcategory" class="form-input" required>@foreach(['production','tourism','recreation','water_regulation','carbon_sequestration','existence_value','bequest_value'] as $s)<option value="{{ $s }}" {{ $benefit->subcategory === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label class="form-label">Deskripsi</label><input type="text" name="description" value="{{ old('description', $benefit->description) }}" required class="form-input"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Nilai (Rp)</label><input type="text" name="value" value="{{ old('value', $benefit->value) }}" required class="form-input rupiah-input"></div>
                <div class="form-group"><label class="form-label">Metode</label><select name="method_used" class="form-input">@foreach(['EOP','TCM','CVM','RC','Manual'] as $m)<option value="{{ $m }}" {{ $benefit->method_used === $m ? 'selected' : '' }}>{{ $m }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label class="form-label">Sumber Data</label><select name="data_source" class="form-input" required>@foreach(['eop','tcm','cvm','manual','literature'] as $ds)<option value="{{ $ds }}" {{ $benefit->data_source === $ds ? 'selected' : '' }}>{{ ucfirst($ds) }}</option>@endforeach</select></div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
