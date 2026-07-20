@extends('layouts.admin')
@section('page_title', 'Edit Koefisien')
@section('admin_content')
<div style="max-width:600px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.master.coefficients.index') }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.master.coefficients.update', $coefficient) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Kode</label><input type="text" name="code" value="{{ old('code', $coefficient->code) }}" required class="form-input"></div>
                <div class="form-group"><label class="form-label">Nama</label><input type="text" name="name" value="{{ old('name', $coefficient->name) }}" required class="form-input"></div>
            </div>
            <div class="form-group"><label class="form-label">Deskripsi</label><textarea name="description" class="form-input" rows="2">{{ old('description', $coefficient->description) }}</textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Nilai</label><input type="number" name="value" value="{{ old('value', $coefficient->value) }}" required step="0.0001" class="form-input"></div>
                <div class="form-group"><label class="form-label">Satuan</label><input type="text" name="unit" value="{{ old('unit', $coefficient->unit) }}" required class="form-input"></div>
                <div class="form-group"><label class="form-label">Tipe</label><input type="text" name="type" value="{{ old('type', $coefficient->type) }}" required class="form-input"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Sumber</label><input type="text" name="source" value="{{ old('source', $coefficient->source) }}" class="form-input"></div>
                <div class="form-group"><label class="form-label">Tahun</label><input type="number" name="year" value="{{ old('year', $coefficient->year) }}" class="form-input"></div>
            </div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.master.coefficients.index') }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
