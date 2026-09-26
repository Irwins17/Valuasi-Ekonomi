import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';
import { DATA_COLLECTION_TYPES, collectionMethodsFor } from '../../../../lib/dataCollectionTypes';

export default function Form({ project, record, riskTypes, defensiveActions }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.abm.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        respondent_code: record?.respondent_code || '',
        location: record?.location || '',
        risk_type: record?.risk_type || '',
        exposure_condition: record?.exposure_condition || '',
        defensive_action: record?.defensive_action || '',
        defensive_goods: record?.defensive_goods || '',
        quantity: record?.quantity ?? '',
        unit_price: record?.unit_price ?? '',
        time_cost: record?.time_cost ?? '',
        medical_cost: record?.medical_cost ?? '',
        sick_days: record?.sick_days ?? '',
        daily_wage: record?.daily_wage ?? '',
        household_size: record?.household_size ?? '',
        affected_population: record?.affected_population ?? '',
        data_source: record?.data_source || '',
        data_collection_type: record?.data_collection_type || '',
        collection_method: record?.collection_method || '',
        notes: record?.notes || '',
    });

    const defensive = toNumber(data.quantity) * toNumber(data.unit_price) + toNumber(data.time_cost);
    const lostIncome = toNumber(data.sick_days) * toNumber(data.daily_wage);
    const totalAvoidance = defensive + toNumber(data.medical_cost) + lostIncome;

    const fields = [
        { name: 'respondent_code', label: 'ID Responden / RT', type: 'text', required: true, placeholder: 'Contoh: ABM-0015' },
        { name: 'location', label: 'Lokasi', type: 'text', required: true, placeholder: 'Contoh: Desa Sumberrejo' },
        { name: 'risk_type', label: 'Jenis Risiko / Pencemaran', type: 'select', required: true, options: riskTypes, placeholder: '-- Pilih jenis risiko --' },
        { name: 'exposure_condition', label: 'Kondisi Lingkungan / Paparan', type: 'text', placeholder: 'Contoh: Air sumur keruh sepanjang musim hujan' },
        { name: 'defensive_action', label: 'Tindakan Defensif', type: 'select', required: true, options: defensiveActions, placeholder: '-- Pilih tindakan defensif --' },
        { name: 'defensive_goods', label: 'Jenis Barang / Jasa Defensif', type: 'text', placeholder: 'Contoh: Galon air 19L' },
        { name: 'quantity', label: 'Kuantitas', type: 'number', placeholder: 'Contoh: 24', suffix: 'unit/tahun' },
        { name: 'unit_price', label: 'Harga per Unit', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 20.000' },
        { name: 'time_cost', label: 'Biaya Waktu Pencegahan', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 300.000', hint: 'Nilai waktu yang dikorbankan untuk tindakan pencegahan.' },
        { name: 'medical_cost', label: 'Biaya Medis', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 750.000' },
        { name: 'sick_days', label: 'Hari Sakit / Tidak Bekerja', type: 'number', placeholder: 'Contoh: 5', suffix: 'hari/tahun' },
        { name: 'daily_wage', label: 'Upah per Hari', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 100.000' },
        { name: 'household_size', label: 'Jumlah Anggota RT', type: 'number', step: '1', placeholder: 'Contoh: 4', suffix: 'orang' },
        { name: 'affected_population', label: 'Total Populasi Terdampak', type: 'number', step: '1', placeholder: 'Contoh: 12000', suffix: 'orang' },
        { name: 'data_source', label: 'Sumber Data', type: 'text', placeholder: 'Contoh: Survei rumah tangga 2026' },
        { name: 'data_collection_type', label: 'Jenis Data', type: 'select', options: DATA_COLLECTION_TYPES.map((t) => [t.value, t.label]), placeholder: '-- Pilih jenis data --' },
        { name: 'collection_method', label: 'Metode Pengumpulan', type: 'select', options: collectionMethodsFor(data.data_collection_type).map((m) => [m.value, m.label]), placeholder: '-- Pilih metode --' },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.abm.update', [project.id, record.id]));
        else post(route('admin.modules.abm.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data ABM / Defensive Expenditure`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data ABM"
            formTitle={`Form Input ABM — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula ABM"
                        formulas={[
                            'H = g(E, A, Z)',
                            'Nilai Averting = Σ(Q × P) + biaya waktu',
                            'Pendapatan Hilang = hari sakit × upah harian',
                            'Total Avoidance = defensif + medis + pendapatan hilang',
                        ]}
                        legend={[
                            { sym: 'Q', desc: 'kuantitas barang/jasa defensif' },
                            { sym: 'P', desc: 'harga per unit' },
                            { sym: 'H', desc: 'fungsi kesehatan atas paparan, tindakan, dan karakteristik' },
                        ]}
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Total Pengeluaran Defensif', sublabel: '(Q × P) + biaya waktu', value: defensive, format: 'currency', unit: 'per tahun', color: '#6366f1' },
                            { label: 'Pendapatan Hilang', sublabel: 'Hari sakit × upah harian', value: lostIncome, format: 'currency', unit: 'per tahun', color: '#f59e0b' },
                            { label: 'Total Avoidance Value', sublabel: 'Defensif + medis + pendapatan hilang', value: totalAvoidance, format: 'currency', unit: 'per tahun', color: '#10b981' },
                        ]}
                        note="Ketiga nilai dihitung langsung dari input dan disimpan bersama data."
                    />
                </>
            }
        />
    );
}
