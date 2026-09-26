import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';

export default function Form({ project, record, statuses, serviceCategories }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.adc.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        record_code: record?.record_code || '',
        service_category: record?.service_category || 'regulating',
        damage_type: record?.damage_type || '',
        location: record?.location || '',
        protected_area: record?.protected_area ?? '',
        damage_cost_per_unit: record?.damage_cost_per_unit ?? '',
        event_probability: record?.event_probability ?? 1,
        period_year: record?.period_year ?? '',
        data_source: record?.data_source || '',
        data_status: record?.data_status || 'draft',
        notes: record?.notes || '',
    });

    const avoided = toNumber(data.protected_area) * toNumber(data.damage_cost_per_unit) * toNumber(data.event_probability);

    const fields = [
        { name: 'record_code', label: 'ID Data', type: 'text', required: true, placeholder: 'Contoh: ADC-0001' },
        { name: 'service_category', label: 'Kategori Jasa', type: 'select', required: true, options: Object.entries(serviceCategories) },
        { name: 'damage_type', label: 'Jenis Kerusakan Potensial', type: 'text', required: true, placeholder: 'Contoh: Banjir, abrasi, intrusi air laut' },
        { name: 'location', label: 'Lokasi', type: 'text', required: true, placeholder: 'Contoh: Desa pesisir X' },
        { name: 'protected_area', label: 'Luas Area Terlindungi (ha)', type: 'number', required: true, suffix: 'ha', placeholder: 'Contoh: 50' },
        { name: 'damage_cost_per_unit', label: 'Estimasi Biaya Kerusakan per ha Tanpa Ekosistem', type: 'currency', required: true, prefix: 'Rp', placeholder: 'Contoh: 15.000.000' },
        { name: 'event_probability', label: 'Probabilitas Kejadian per Tahun (0–1)', type: 'number', required: true, step: '0.01', placeholder: 'Contoh: 0,2', hint: '1 = pasti terjadi tiap tahun, 0,2 = rata-rata sekali per 5 tahun.' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year', required: true },
        { name: 'data_source', label: 'Sumber Data', type: 'text', required: true, placeholder: 'Contoh: Laporan bencana, studi risiko' },
        { name: 'data_status', label: 'Status Data', type: 'select', required: true, options: Object.entries(statuses) },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500 },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.adc.update', [project.id, record.id]));
        else post(route('admin.modules.adc.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Avoided Damage Cost`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data Avoided Damage Cost"
            formTitle={`Form Input Avoided Damage Cost — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula Avoided Damage Cost"
                        formulas={['ADC = Luas Terlindungi × Biaya Kerusakan per Unit × Probabilitas Kejadian']}
                        legend={[
                            { sym: 'Luas Terlindungi', desc: 'Area yang terlindungi oleh ekosistem (ha)' },
                            { sym: 'Biaya Kerusakan/Unit', desc: 'Estimasi biaya kerusakan per ha bila ekosistem tidak ada' },
                            { sym: 'Probabilitas', desc: 'Peluang kejadian kerusakan per tahun (0–1)' },
                        ]}
                        note="Berbeda dari ABM (biaya defensif rumah tangga) — ADC menilai kerusakan skala kawasan yang dihindari."
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Nilai Kerusakan Dihindari', sublabel: 'Luas × Biaya × Probabilitas', value: avoided, format: 'currency', unit: 'per tahun', color: '#10b981' },
                        ]}
                    />
                </>
            }
        />
    );
}
