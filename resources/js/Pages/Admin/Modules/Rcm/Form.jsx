import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';

export default function Form({ project, record, statuses, serviceCategories }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.rcm.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        record_code: record?.record_code || '',
        service_category: record?.service_category || 'regulating',
        asset_type: record?.asset_type || '',
        location: record?.location || '',
        quantity: record?.quantity ?? '',
        unit: record?.unit || '',
        replacement_cost_per_unit: record?.replacement_cost_per_unit ?? '',
        useful_life_years: record?.useful_life_years ?? '',
        period_year: record?.period_year ?? '',
        data_source: record?.data_source || '',
        data_status: record?.data_status || 'draft',
        notes: record?.notes || '',
    });

    const total = toNumber(data.quantity) * toNumber(data.replacement_cost_per_unit);
    const life = toNumber(data.useful_life_years);
    const annual = life > 0 ? total / life : total;

    const fields = [
        { name: 'record_code', label: 'ID Data', type: 'text', required: true, placeholder: 'Contoh: RCM-0001' },
        { name: 'service_category', label: 'Kategori Jasa', type: 'select', required: true, options: Object.entries(serviceCategories) },
        { name: 'asset_type', label: 'Aset/Infrastruktur Alami yang Digantikan', type: 'text', required: true, placeholder: 'Contoh: Penahan gelombang alami (mangrove)' },
        { name: 'location', label: 'Lokasi', type: 'text', required: true, placeholder: 'Contoh: Pesisir Desa Watu Karung' },
        { name: 'quantity', label: 'Volume/Luas Aset yang Digantikan', type: 'number', required: true, placeholder: 'Contoh: 500' },
        { name: 'unit', label: 'Satuan', type: 'text', required: true, placeholder: 'Contoh: m, ha, m³' },
        { name: 'replacement_cost_per_unit', label: 'Biaya Penggantian per Unit', type: 'currency', required: true, prefix: 'Rp', placeholder: 'Contoh: 2.500.000' },
        { name: 'useful_life_years', label: 'Umur Manfaat (tahun)', type: 'number', placeholder: 'Contoh: 20', hint: 'Kosongkan bila nilai tidak dianualisasi.' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year', required: true },
        { name: 'data_source', label: 'Sumber Data', type: 'text', required: true, placeholder: 'Contoh: Survei lapangan, dokumen teknis' },
        { name: 'data_status', label: 'Status Data', type: 'select', required: true, options: Object.entries(statuses) },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500 },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.rcm.update', [project.id, record.id]));
        else post(route('admin.modules.rcm.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Replacement Cost`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data Replacement Cost"
            formTitle={`Form Input Replacement Cost — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula Replacement Cost"
                        formulas={['Nilai total = Volume/Luas × Biaya Penggantian per Unit', 'Nilai tahunan = Nilai total ÷ Umur Manfaat']}
                        legend={[
                            { sym: 'Volume/Luas', desc: 'Ukuran aset alami yang digantikan' },
                            { sym: 'Biaya/Unit', desc: 'Biaya membangun aset buatan setara per unit' },
                            { sym: 'Umur Manfaat', desc: 'Lama aset pengganti berfungsi (tahun)' },
                        ]}
                        note="Modul mandiri, terpisah dari label 'Replacement Cost' pada modul Erosion Control."
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Nilai Total', sublabel: 'Volume/Luas × Biaya/Unit', value: total, format: 'currency', color: '#6366f1' },
                            { label: 'Nilai Tahunan', sublabel: 'Nilai total ÷ Umur Manfaat', value: annual, format: 'currency', unit: 'per tahun', color: '#10b981' },
                        ]}
                    />
                </>
            }
        />
    );
}
