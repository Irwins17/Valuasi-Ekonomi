import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../Components/modules/ModuleFormShell';
import OutputPreview from '../../../Components/modules/OutputPreview';
import FormulaPanel from '../../../Components/modules/FormulaPanel';
import { presentValue } from '../../../lib/valuation';

const CATEGORIES = [
    ['direct_cost', 'Direct Cost'],
    ['indirect_cost', 'Indirect Cost'],
];
const SUBCATEGORIES = ['investment', 'operation_maintenance', 'opportunity_cost', 'externality', 'other'];
const DATA_STATUSES = [
    ['draft', 'Draft — belum diverifikasi'],
    ['verified', 'Verified — sudah diperiksa'],
];

/** Shared create/edit form for a project cost. */
export default function CostForm({ project, cost, activityGroups = {}, valuationSettings }) {
    const isEdit = Boolean(cost);
    const backHref = route('admin.projects.show', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        category: cost?.category || 'direct_cost',
        subcategory: cost?.subcategory || 'investment',
        activity_group: cost?.activity_group || '',
        description: cost?.description || '',
        value: cost?.value ?? '',
        year_applied: cost?.year_applied ?? '',
        payment_type: cost?.payment_type || '',
        calculation_method: cost?.calculation_method || '',
        responsible_party: cost?.responsible_party || '',
        data_status: cost?.data_status || 'verified',
        calculation_notes: cost?.calculation_notes || '',
    });

    const pv = presentValue(data.value, data.year_applied, valuationSettings.base_year, valuationSettings.discount_rate);
    const discountedYears = Math.max(0, Number(data.year_applied || valuationSettings.base_year) - valuationSettings.base_year);

    const fields = [
        { name: 'category', label: 'Kategori', type: 'select', required: true, options: CATEGORIES },
        { name: 'subcategory', label: 'Subkategori', type: 'select', required: true, options: SUBCATEGORIES.map((s) => [s, s.replace(/_/g, ' ').replace(/^./, (c) => c.toUpperCase())]) },
        {
            name: 'activity_group', label: 'Kelompok Kegiatan', type: 'select', full: true,
            options: Object.entries(activityGroups),
            placeholder: '— Belum dikelompokkan —',
            hint: 'Biaya produksi yang sudah dikurangkan di dalam modul (mis. Ci pada EOP) tidak dicatat lagi di sini.',
        },
        { name: 'description', label: 'Deskripsi', type: 'text', required: true, full: true, placeholder: 'Deskripsi biaya' },
        { name: 'value', label: 'Nilai (Rp)', type: 'currency', required: true, prefix: 'Rp', placeholder: '50.000' },
        { name: 'year_applied', label: 'Tahun Berlaku', type: 'year', hint: `Kosong = tahun dasar (${valuationSettings.base_year}), faktor diskonto 1.` },
        { name: 'payment_type', label: 'Tipe Pembayaran', type: 'text', placeholder: 'Investasi Awal / Tahunan' },
        { name: 'calculation_method', label: 'Metode Perhitungan', type: 'text', placeholder: 'Contoh: Harga satuan × volume, RAB, kontrak' },
        { name: 'responsible_party', label: 'Penanggung Jawab', type: 'text', full: true, placeholder: 'Contoh: Dinas Lingkungan Hidup, KKP, mitra swasta' },
        { name: 'data_status', label: 'Status Data', type: 'select', required: true, options: DATA_STATUSES, hint: 'Draft menandai angka yang masih perlu diperiksa.' },
        { name: 'calculation_notes', label: 'Catatan', type: 'textarea', full: true, placeholder: 'Asumsi, sumber angka, atau catatan verifikasi' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.costs.update', [project.id, cost.id]));
        else post(route('admin.costs.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Biaya`}
            projectName={project.name}
            backHref={backHref}
            backLabel="Kembali ke Proyek"
            formTitle={`Form Biaya — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            submitLabel={isEdit ? 'Perbarui' : 'Simpan'}
            sidebar={
                <>
                    <OutputPreview
                        title="Pratinjau Present Value"
                        rows={[
                            { label: 'Nilai Nominal', sublabel: 'Sebelum diskonto', value: data.value, format: 'currency', color: '#f59e0b' },
                            { label: 'Present Value', sublabel: `Didiskontokan ${discountedYears} tahun`, value: pv, format: 'currency', unit: `tahun dasar ${valuationSettings.base_year}`, color: '#ef4444' },
                        ]}
                        note={`Dihitung ulang di server saat disimpan memakai discount rate proyek (${valuationSettings.discount_rate}%). Biaya tanpa tahun dipakai apa adanya.`}
                    />
                    <FormulaPanel
                        title="Formula Present Value"
                        formulas={['PV = Biaya / (1 + r)ⁿ', 'n = Tahun Berlaku − Tahun Dasar']}
                        legend={[
                            { sym: 'r', desc: `Discount rate proyek (${valuationSettings.discount_rate}%)` },
                            { sym: 'n', desc: `Selisih tahun terhadap ${valuationSettings.base_year}; minimal 0` },
                        ]}
                        note="BCR membandingkan PV manfaat terhadap PV biaya, sehingga keduanya harus didiskontokan ke tahun dasar yang sama."
                    />
                </>
            }
        />
    );
}
