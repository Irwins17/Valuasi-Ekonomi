@extends('layouts.admin')
@section('page_title', 'Tambah Data CVM')
@section('admin_content')
<div style="max-width:700px;margin:0 auto;" class="animate-fade-up">
    <a href="{{ route('admin.modules.cvm.index', $project) }}" style="color:var(--text-muted);text-decoration:none;font-size:13px;display:block;margin-bottom:16px;">← Kembali</a>
    <div class="card">
        <h3 style="font-size:16px;font-weight:700;margin-bottom:20px;">Form Input CVM — {{ $project->name }}</h3>
        <form method="POST" action="{{ route('admin.modules.cvm.store', $project) }}">
            @csrf
            <div class="form-group"><label class="form-label">ID Responden (Nomor)</label><input type="number" name="respondent_id" value="{{ old('respondent_id') }}" required min="1" class="form-input" placeholder="2001">@error('respondent_id')<p class="form-error">{{ $message }}</p>@enderror</div>
            <div class="form-group">
                <label class="form-label">Bersedia Membayar?</label>
                <select name="willing_to_pay" class="form-input" required id="wtpSelect" onchange="document.getElementById('wtpFields').style.display=this.value==='1'?'block':'none';document.getElementById('unwillingFields').style.display=this.value==='0'?'block':'none';">
                    <option value="1" {{ old('willing_to_pay','1') === '1' ? 'selected' : '' }}>Ya</option>
                    <option value="0" {{ old('willing_to_pay') === '0' ? 'selected' : '' }}>Tidak</option>
                </select>
            </div>
            <div id="wtpFields"><div class="form-group"><label class="form-label">Nilai WTP (Rp)</label><input type="text" name="wtp" value="{{ old('wtp') }}" class="form-input rupiah-input" placeholder="50.000">@error('wtp')<p class="form-error">{{ $message }}</p>@enderror</div></div>
            <div id="unwillingFields" style="display:none;"><div class="form-group"><label class="form-label">Alasan Tidak Bersedia</label><textarea name="reason_if_unwilling" class="form-input" rows="2">{{ old('reason_if_unwilling') }}</textarea></div></div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Jumlah Anggota RT</label><input type="number" name="household_size" value="{{ old('household_size') }}" min="1" class="form-input"></div>
                <div class="form-group"><label class="form-label">Pendapatan RT (Rp)</label><input type="text" name="household_income" value="{{ old('household_income') }}" class="form-input rupiah-input" placeholder="3.000.000"></div>
            </div>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group"><label class="form-label">Tingkat Pendidikan</label><select name="education_level" class="form-input"><option value="">-- Pilih --</option><option value="SD">SD</option><option value="SMP">SMP</option><option value="SMA">SMA</option><option value="D3">D3</option><option value="S1">S1</option><option value="S2">S2</option><option value="S3">S3</option></select></div>
                <div class="form-group"><label class="form-label">Lokasi Responden</label><input type="text" name="respondent_location" value="{{ old('respondent_location') }}" class="form-input"></div>
            </div>
            <div style="display:flex;gap:10px;padding-top:20px;border-top:1px solid var(--border-light);"><button type="submit" class="btn btn-primary">Simpan</button><a href="{{ route('admin.modules.cvm.index', $project) }}" class="btn btn-ghost">Batal</a></div>
        </form>
    </div>
</div>
@endsection
