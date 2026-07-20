@extends('layouts.admin')
@section('page_title', 'Edit Cost')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.projects.show', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.costs.update', [$project, $cost]) }}">
            @csrf @method('PUT')
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Kategori</label><select name="category" class="form-input" required><option value="direct_cost" {{ $cost->category === 'direct_cost' ? 'selected' : '' }}>Direct Cost</option><option value="indirect_cost" {{ $cost->category === 'indirect_cost' ? 'selected' : '' }}>Indirect Cost</option></select></div>
                <div class="form-group"><label class="form-label">Subkategori</label><select name="subcategory" class="form-input" required>@foreach(['investment','operation_maintenance','opportunity_cost','externality','other'] as $s)<option value="{{ $s }}" {{ $cost->subcategory === $s ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach</select></div>
            </div>
            <div class="form-group"><label class="form-label">Deskripsi</label><input type="text" name="description" value="{{ old('description', $cost->description) }}" required class="form-input"></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Nilai (Rp)</label><input type="text" name="value" value="{{ old('value', $cost->value) }}" required class="form-input rupiah-input"></div>
                <div class="form-group"><label class="form-label">Tipe Pembayaran</label><input type="text" name="payment_type" value="{{ old('payment_type', $cost->payment_type) }}" class="form-input"></div>
            </div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.projects.show', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
