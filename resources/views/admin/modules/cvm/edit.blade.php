@extends('layouts.admin')
@section('page_title', 'Edit Data CVM')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.modules.cvm.index', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <form method="POST" action="{{ route('admin.modules.cvm.update', [$project, $cvmData]) }}">
            @csrf @method('PUT')
            <div class="form-group"><label class="form-label">Bersedia Membayar?</label><select name="willing_to_pay" class="form-input" required><option value="1" {{ $cvmData->willing_to_pay ? 'selected' : '' }}>Ya</option><option value="0" {{ !$cvmData->willing_to_pay ? 'selected' : '' }}>Tidak</option></select></div>
            <div class="form-group"><label class="form-label">Nilai WTP (Rp)</label><input type="text" name="wtp" value="{{ old('wtp', $cvmData->wtp) }}" class="form-input rupiah-input"></div>
            <div class="form-group"><label class="form-label">Alasan (jika tidak bersedia)</label><textarea name="reason_if_unwilling" class="form-input" rows="2">{{ old('reason_if_unwilling', $cvmData->reason_if_unwilling) }}</textarea></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Jumlah Anggota RT</label><input type="number" name="household_size" value="{{ old('household_size', $cvmData->household_size) }}" class="form-input"></div>
                <div class="form-group"><label class="form-label">Pendapatan RT (Rp)</label><input type="text" name="household_income" value="{{ old('household_income', $cvmData->household_income) }}" class="form-input rupiah-input"></div>
            </div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Perbarui</button><a href="{{ route('admin.modules.cvm.index', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
