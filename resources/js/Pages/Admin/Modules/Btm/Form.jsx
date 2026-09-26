import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';

export default function Form({ project, record, statuses, serviceCategories }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.btm.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        record_code: record?.record_code || '',
        service_category: record?.service_category || 'regulating',
        source_study_title: record?.source_study_title || '',
        source_study_location: record?.source_study_location || '',
        source_study_year: record?.source_study_year ?? '',
        source_value: record?.source_value ?? '',
        adjustment_factor: record?.adjustment_factor ?? 1,
        target_quantity: record?.target_quantity ?? 1,
        validity_notes: record?.validity_notes || '',
        period_year: record?.period_year ?? '',
        data_source: record?.data_source || '',
        data_status: record?.data_status || 'draft',
        notes: record?.notes || '',
    });

    const transferred = toNumber(data.source_value) * toNumber(data.adjustment_factor) * toNumber(data.target_quantity);

    const fields = [
        { name: 'record_code', label: 'ID Data', type: 'text', required: true, placeholder: 'Contoh: BTM-0001' },
        { name: 'service_category', label: 'Kategori Jasa', type: 'select', required: true, options: Object.entries(serviceCategories) },
        { name: 'source_study_title', label: 'Judul Studi Sumber', type: 'text', required: true, full: true, placeholder: 'Contoh: Valuasi Ekonomi Mangrove Segara Anakan (2019)' },
        { name: 'source_study_location', label: 'Lokasi Studi Sumber', type: 'text', placeholder: 'Contoh: Segara Anakan, Cilacap' },
        { name: 'source_study_year', label: 'Tahun Studi Sumber', type: 'year' },
        { name: 'source_value', label: 'Nilai Asli Studi Sumber (per unit)', type: 'currency', required: true, prefix: 'Rp', placeholder: 'Contoh: 12.000.000' },
        { name: 'adjustment_factor', label: 'Faktor Penyesuaian', type: 'number', required: true, step: '0.01', placeholder: 'Contoh: 1,05', hint: 'Indeks harga, PPP, atau rasio pendapatan antar lokasi. 1 = tanpa penyesuaian.' },
        { name: 'target_quantity', label: 'Kuantitas / Luas Lokasi Target', type: 'number', required: true, placeholder: 'Contoh: 25' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year', required: true },
        { name: 'data_source', label: 'Sumber Data', type: 'text', required: true, placeholder: 'Contoh: Jurnal, laporan penelitian' },
        { name: 'data_status', label: 'Status Data', type: 'select', required: true, options: Object.entries(statuses) },
        { name: 'validity_notes', label: 'Catatan Validitas Transfer', type: 'textarea', full: true, placeholder: 'Kesesuaian konteks ekosistem, populasi, dan kondisi sosial-ekonomi lokasi sumber vs target' },
        { name: 'notes', label: 'Catatan Lain (opsional)', type: 'textarea', full: true, maxLength: 500 },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.btm.update', [project.id, record.id]));
        else post(route('admin.modules.btm.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Benefit Transfer`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data Benefit Transfer"
            formTitle={`Form Input Benefit Transfer — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula Benefit Transfer"
                        formulas={['Nilai transfer = Nilai Studi Sumber × Faktor Penyesuaian × Kuantitas Target']}
                        legend={[
                            { sym: 'Nilai Sumber', desc: 'Nilai per unit dari studi valuasi lain' },
                            { sym: 'Faktor Penyesuaian', desc: 'Penyesuaian konteks (harga, PPP, pendapatan)' },
                            { sym: 'Kuantitas Target', desc: 'Skala lokasi/proyek saat ini' },
                        ]}
                        note="Gunakan hanya bila pengukuran langsung tidak tersedia; dokumentasikan kesesuaian konteks pada Catatan Validitas Transfer."
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Nilai Transfer', sublabel: 'Sumber × Faktor × Kuantitas', value: transferred, format: 'currency', color: '#10b981' },
                        ]}
                    />
                </>
            }
        />
    );
}
