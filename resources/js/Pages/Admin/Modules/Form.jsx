import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import ConfirmDeleteButton from '../../../Components/ui/ConfirmDeleteButton';

export default function Form({ project, module, methodGroups, serviceCategories, statuses }) {
    const isEdit = Boolean(module);
    const isBuiltin = Boolean(module?.is_builtin);

    const { data, setData, post, put, processing, errors } = useForm({
        name: module?.name || '',
        code: module?.code || '',
        valuation_method: module?.valuation_method || '',
        method_group: module?.method_group || '',
        service_category: module?.service_category || '',
        subcategory: module?.subcategory || '',
        formula_summary: module?.formula_summary || '',
        input_variables: module?.input_variables || '',
        output_unit: module?.output_unit || '',
        data_source: module?.data_source || '',
        status: module?.status || 'draft',
        description: module?.description || '',
        show_on_project_detail: module ? Boolean(module.show_on_project_detail) : true,
    });

    function submit(e) {
        e.preventDefault();
        if (isEdit) {
            put(route('admin.modules.update', [project.id, module.code]));
        } else {
            post(route('admin.modules.store', project.id));
        }
    }

    const title = isEdit ? `Konfigurasi Modul — ${module.name}` : 'Tambah Modul Valuasi';

    return (
        <AdminLayout title={title} subtitle={`Proyek ${project.code} — ${project.name}`}>
            <Head title={title} />

            <div className="animate-fade-up">
                <Link href={route('admin.modules.index', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>
                    ← Kembali ke Modul Valuasi
                </Link>

                <div style={{ display: 'grid', gridTemplateColumns: 'minmax(0,1fr) 300px', gap: 16, alignItems: 'start' }}>
                    <div className="card">
                        <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>
                            {isEdit ? 'Konfigurasi Modul' : 'Form Modul Baru'} — {project.name}
                        </h3>

                        <form onSubmit={submit}>
                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                <div className="form-group">
                                    <label className="form-label">Nama Modul <span style={{ color: 'var(--danger)' }}>*</span></label>
                                    <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" placeholder="Contoh: Nilai Perikanan Tangkap" />
                                    {errors.name && <p className="form-error">{errors.name}</p>}
                                </div>
                                <div className="form-group">
                                    <label className="form-label">Kode Modul <span style={{ color: 'var(--danger)' }}>*</span></label>
                                    <input
                                        type="text"
                                        value={data.code}
                                        onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                        required
                                        readOnly={isEdit}
                                        className="form-input"
                                        placeholder="Contoh: FISH"
                                        style={isEdit ? { background: 'var(--surface-alt)', color: 'var(--text-muted)', cursor: 'not-allowed' } : undefined}
                                    />
                                    <p className="form-hint">{isEdit ? 'Kode modul tidak dapat diubah.' : 'Huruf, angka, - dan _ saja. Otomatis huruf kapital.'}</p>
                                    {errors.code && <p className="form-error">{errors.code}</p>}
                                </div>
                            </div>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                <div className="form-group">
                                    <label className="form-label">Metode Valuasi</label>
                                    <input type="text" value={data.valuation_method} onChange={(e) => setData('valuation_method', e.target.value)} className="form-input" placeholder="Contoh: EOP, TCM, Harga Pasar" />
                                    {errors.valuation_method && <p className="form-error">{errors.valuation_method}</p>}
                                </div>
                                <div className="form-group">
                                    <label className="form-label">Kelompok Metode</label>
                                    <select value={data.method_group} onChange={(e) => setData('method_group', e.target.value)} className="form-input">
                                        <option value="">— Pilih kelompok metode —</option>
                                        {Object.entries(methodGroups).map(([key, label]) => (
                                            <option key={key} value={key}>{label}</option>
                                        ))}
                                    </select>
                                    {errors.method_group && <p className="form-error">{errors.method_group}</p>}
                                </div>
                            </div>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                <div className="form-group">
                                    <label className="form-label">Jenis Jasa Ekosistem</label>
                                    <select value={data.service_category} onChange={(e) => setData('service_category', e.target.value)} className="form-input">
                                        <option value="">— Pilih jasa ekosistem —</option>
                                        {Object.entries(serviceCategories).map(([key, label]) => (
                                            <option key={key} value={key}>{label}</option>
                                        ))}
                                    </select>
                                    {errors.service_category && <p className="form-error">{errors.service_category}</p>}
                                </div>
                                <div className="form-group">
                                    <label className="form-label">Subkategori / Tujuan Penilaian</label>
                                    <input type="text" value={data.subcategory} onChange={(e) => setData('subcategory', e.target.value)} className="form-input" placeholder="Contoh: Nilai guna langsung perikanan" />
                                    {errors.subcategory && <p className="form-error">{errors.subcategory}</p>}
                                </div>
                            </div>

                            <div className="form-group">
                                <label className="form-label">Rumus Ringkas</label>
                                <textarea value={data.formula_summary} onChange={(e) => setData('formula_summary', e.target.value)} className="form-input" rows={3} placeholder={'Contoh:\nNilai = Q × P\nTotal = Nilai × Luas Area'} style={{ fontFamily: 'ui-monospace, SFMono-Regular, Menlo, monospace', fontSize: 13 }} />
                                <p className="form-hint">Satu baris per persamaan. Ditampilkan pada panel formula modul.</p>
                                {errors.formula_summary && <p className="form-error">{errors.formula_summary}</p>}
                            </div>

                            <div className="form-group">
                                <label className="form-label">Variabel Input Utama</label>
                                <textarea value={data.input_variables} onChange={(e) => setData('input_variables', e.target.value)} className="form-input" rows={2} placeholder="Contoh: Kuantitas (Q), Harga pasar (P), Luas area" style={{ minHeight: 68 }} />
                                {errors.input_variables && <p className="form-error">{errors.input_variables}</p>}
                            </div>

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                                <div className="form-group">
                                    <label className="form-label">Satuan Output</label>
                                    <input type="text" value={data.output_unit} onChange={(e) => setData('output_unit', e.target.value)} className="form-input" placeholder="Contoh: Rp/tahun, Rp/ha/tahun" />
                                    {errors.output_unit && <p className="form-error">{errors.output_unit}</p>}
                                </div>
                                <div className="form-group">
                                    <label className="form-label">Sumber Data</label>
                                    <input type="text" value={data.data_source} onChange={(e) => setData('data_source', e.target.value)} className="form-input" placeholder="Contoh: Survei lapangan, BPS, literatur" />
                                    {errors.data_source && <p className="form-error">{errors.data_source}</p>}
                                </div>
                            </div>

                            <div className="form-group">
                                <label className="form-label">Status Modul <span style={{ color: 'var(--danger)' }}>*</span></label>
                                <select value={data.status} onChange={(e) => setData('status', e.target.value)} className="form-input" required style={{ maxWidth: 260 }}>
                                    {Object.entries(statuses).map(([key, label]) => (
                                        <option key={key} value={key}>{label}</option>
                                    ))}
                                </select>
                                {errors.status && <p className="form-error">{errors.status}</p>}
                            </div>

                            <div className="form-group">
                                <label className="form-label">Deskripsi / Catatan</label>
                                <textarea value={data.description} onChange={(e) => setData('description', e.target.value)} className="form-input" rows={3} maxLength={2000} placeholder="Penjelasan singkat mengenai modul dan cara penggunaannya." />
                                {errors.description && <p className="form-error">{errors.description}</p>}
                            </div>

                            <div className="form-group">
                                <label style={{ display: 'flex', alignItems: 'flex-start', gap: 10, cursor: 'pointer' }}>
                                    <input
                                        type="checkbox"
                                        checked={data.show_on_project_detail}
                                        onChange={(e) => setData('show_on_project_detail', e.target.checked)}
                                        style={{ width: 16, height: 16, marginTop: 2, accentColor: 'var(--primary)', cursor: 'pointer' }}
                                    />
                                    <span>
                                        <span style={{ fontWeight: 600, fontSize: 14 }}>Tampilkan di Detail Proyek</span>
                                        <span style={{ display: 'block', fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>
                                            Modul muncul pada bagian “Status Modul” di halaman detail proyek.
                                        </span>
                                    </span>
                                </label>
                            </div>

                            <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                                <button type="submit" className="btn btn-primary" disabled={processing}>
                                    {processing ? 'Menyimpan…' : 'Simpan'}
                                </button>
                                <Link href={route('admin.modules.index', project.id)} className="btn btn-ghost">Batal</Link>
                                {isEdit && (
                                    <div style={{ marginLeft: 'auto' }}>
                                        <ConfirmDeleteButton
                                            href={route('admin.modules.destroy', [project.id, module.code])}
                                            message={isBuiltin
                                                ? `Kembalikan konfigurasi modul ${module.code} ke pengaturan bawaan?`
                                                : `Hapus modul ${module.code}? Tindakan ini tidak menghapus data proyek lain.`}
                                        >
                                            {isBuiltin ? 'Reset ke Bawaan' : 'Hapus Modul'}
                                        </ConfirmDeleteButton>
                                    </div>
                                )}
                            </div>
                        </form>
                    </div>

                    <div className="card">
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 12 }}>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2"><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></svg>
                            <h3 style={{ fontSize: 14, fontWeight: 700 }}>Panduan Modul</h3>
                        </div>
                        <p style={{ fontSize: 12, color: 'var(--text-muted)', lineHeight: 1.6, marginBottom: 12 }}>
                            {isBuiltin
                                ? 'Modul ini merupakan modul bawaan sistem. Perubahan di sini hanya berlaku untuk proyek ini; kode dan halaman data modul tetap mengikuti bawaan.'
                                : 'Modul kustom digunakan untuk mencatat metode valuasi yang belum tersedia sebagai modul bawaan. Modul akan muncul pada daftar modul dan — bila diaktifkan — pada detail proyek.'}
                        </p>
                        <div style={{ borderTop: '1px solid var(--border)', paddingTop: 12 }}>
                            <div style={{ fontWeight: 700, fontSize: 12.5, marginBottom: 6 }}>Status Modul</div>
                            <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.55 }}>
                                <strong style={{ color: '#10b981' }}>Aktif</strong> — modul siap digunakan dan berkontribusi pada perhitungan valuasi.
                            </p>
                            <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.55, marginTop: 6 }}>
                                <strong style={{ color: '#f59e0b' }}>Draft</strong> — modul disimpan sebagai draf dan belum digunakan dalam analisis.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
