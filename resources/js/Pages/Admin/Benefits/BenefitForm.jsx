import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../Components/modules/ModuleFormShell';
import FormField from '../../../Components/modules/FormField';
import OutputPreview from '../../../Components/modules/OutputPreview';
import FormulaPanel from '../../../Components/modules/FormulaPanel';
import ModuleSourcePicker from './ModuleSourcePicker';
import { VALUATION_TECHNIQUES } from '../../../data/valuationTechniques';
import { presentValue } from '../../../lib/valuation';

const CATEGORIES = [
    ['direct_use', 'Direct Use Value'],
    ['indirect_use', 'Indirect Use Value'],
    ['option_value', 'Option Value'],
    ['existence_value', 'Existence Value'],
    ['bequest_value', 'Bequest Value'],
];
const SUBCATEGORIES = ['production', 'tourism', 'recreation', 'water_regulation', 'carbon_sequestration'];
const LEGACY_METHODS = ['RC', 'Manual'];
const DATA_SOURCES = [
    ['eop', 'EOP'],
    ['tcm', 'TCM'],
    ['cvm', 'CVM'],
    ['duv', 'DUV — Nilai Pasar'],
    ['hpm', 'HPM'],
    ['abm', 'ABM'],
    ['ce', 'CE'],
    ['rcm', 'RCM — Replacement Cost'],
    ['adc', 'ADC — Avoided Damage Cost'],
    ['btm', 'BTM — Benefit Transfer'],
    ['manual', 'Manual'],
    ['literature', 'Literatur'],
];
const DATA_STATUSES = [
    ['draft', 'Draft — belum diverifikasi'],
    ['verified', 'Verified — sudah diperiksa'],
];

/**
 * `data_source` predates the module picker and only knows three methods, so a
 * benefit pulled from DUV/ABM/Jasa Ekosistem is recorded as "manual" there —
 * the precise origin lives in `source_module`, which is the column the
 * traceability actually hangs off.
 */
const DATA_SOURCE_FOR_MODULE = {
    eop: 'eop',
    tcm: 'tcm',
    cvm: 'cvm',
    duv: 'duv',
    hpm: 'hpm',
    abm: 'abm',
    ce: 'ce',
    rcm: 'rcm',
    adc: 'adc',
    btm: 'btm',
};

/** Shared create/edit form for a project benefit. */
export default function BenefitForm({ project, benefit, moduleSources = {}, moduleLabels = {}, serviceGroups = {}, valuationSettings }) {
    const isEdit = Boolean(benefit);
    const backHref = route('admin.projects.show', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        category: benefit?.category || 'direct_use',
        subcategory: benefit?.subcategory || 'production',
        ecosystem_service_group: benefit?.ecosystem_service_group || '',
        description: benefit?.description || '',
        value: benefit?.value ?? '',
        annual_value: benefit?.annual_value ?? '',
        unit: benefit?.unit || '',
        period_year: benefit?.period_year ?? '',
        method_used: benefit?.method_used || 'EOP',
        data_source: benefit?.data_source || 'eop',
        source_module: benefit?.source_module || 'manual',
        source_record_id: benefit?.source_record_id ?? '',
        data_status: benefit?.data_status || 'verified',
        calculation_notes: benefit?.calculation_notes || '',
    });

    const pv = presentValue(data.value, data.period_year, valuationSettings.base_year, valuationSettings.discount_rate);
    const discountedYears = Math.max(0, Number(data.period_year || valuationSettings.base_year) - valuationSettings.base_year);

    function pickModule(moduleKey) {
        // Switching modules invalidates the record, but never the amounts
        // already typed — a half-finished entry should not be wiped by a
        // mis-click on the dropdown.
        setData((current) => ({ ...current, source_module: moduleKey, source_record_id: '' }));
    }

    function pickRecord(recordId) {
        const record = (moduleSources[data.source_module] || []).find((o) => String(o.id) === String(recordId));

        if (!record) {
            setData('source_record_id', '');
            return;
        }

        setData((current) => ({
            ...current,
            source_record_id: record.id,
            description: record.description ?? current.description,
            value: record.value ?? current.value,
            annual_value: record.annual_value ?? current.annual_value,
            period_year: record.period_year ?? '',
            unit: record.unit ?? '',
            ecosystem_service_group: record.ecosystem_service_group ?? current.ecosystem_service_group,
            category: record.category ?? current.category,
            subcategory: record.subcategory ?? current.subcategory,
            method_used: record.method_used ?? current.method_used,
            data_source: DATA_SOURCE_FOR_MODULE[data.source_module] ?? 'manual',
        }));
    }

    const fields = [
        { name: 'category', label: 'Kategori', type: 'select', required: true, options: CATEGORIES },
        { name: 'subcategory', label: 'Subkategori', type: 'select', required: true, options: SUBCATEGORIES.map((s) => [s, s.replace(/_/g, ' ').replace(/^./, (c) => c.toUpperCase())]) },
        { name: 'description', label: 'Deskripsi', type: 'text', required: true, full: true, placeholder: 'Deskripsi manfaat' },
        { name: 'value', label: 'Nilai (Rp)', type: 'currency', required: true, prefix: 'Rp', placeholder: '50.000', hint: 'Nilai yang masuk ke TEV, sebelum didiskontokan.' },
        { name: 'annual_value', label: 'Nilai per Tahun (Rp)', type: 'currency', prefix: 'Rp', hint: 'Angka tahunan dari modul asal; disimpan sebagai rujukan.' },
        { name: 'period_year', label: 'Tahun Berlaku', type: 'year', hint: `Kosong = tahun dasar (${valuationSettings.base_year}), faktor diskonto 1.` },
        { name: 'unit', label: 'Satuan', type: 'text', placeholder: 'Contoh: Rp/tahun, Rp/ha/tahun' },
        { name: 'ecosystem_service_group', label: 'Kelompok Jasa Ekosistem', type: 'select', options: Object.entries(serviceGroups), placeholder: '— Tidak dikelompokkan —' },
        { name: 'data_source', label: 'Sumber Data', type: 'select', required: true, options: DATA_SOURCES },
        { name: 'data_status', label: 'Status Data', type: 'select', required: true, options: DATA_STATUSES, hint: 'Draft menandai angka yang masih perlu diperiksa.' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.benefits.update', [project.id, benefit.id]));
        else post(route('admin.benefits.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Manfaat`}
            projectName={project.name}
            backHref={backHref}
            backLabel="Kembali ke Proyek"
            formTitle={`Form Manfaat — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            submitLabel={isEdit ? 'Perbarui' : 'Simpan'}
            header={
                <>
                    <ModuleSourcePicker
                        sources={moduleSources}
                        labels={moduleLabels}
                        selectedModule={data.source_module}
                        selectedRecordId={data.source_record_id}
                        onPickModule={pickModule}
                        onPickRecord={pickRecord}
                    />
                    {errors.source_record_id && <p className="form-error" style={{ marginTop: -12, marginBottom: 12 }}>{errors.source_record_id}</p>}
                </>
            }
            sidebar={
                <>
                    <OutputPreview
                        title="Pratinjau Present Value"
                        rows={[
                            { label: 'Nilai Nominal', sublabel: 'Sebelum diskonto', value: data.value, format: 'currency', color: '#6366f1' },
                            { label: 'Present Value', sublabel: `Didiskontokan ${discountedYears} tahun`, value: pv, format: 'currency', unit: `tahun dasar ${valuationSettings.base_year}`, color: '#10b981' },
                        ]}
                        note={`Dihitung ulang di server saat disimpan memakai discount rate proyek (${valuationSettings.discount_rate}%). Nilai tanpa tahun dipakai apa adanya.`}
                    />
                    <FormulaPanel
                        title="Formula Present Value"
                        formulas={['PV = Nilai / (1 + r)ⁿ', 'n = Tahun Berlaku − Tahun Dasar']}
                        legend={[
                            { sym: 'r', desc: `Discount rate proyek (${valuationSettings.discount_rate}%)` },
                            { sym: 'n', desc: `Selisih tahun terhadap ${valuationSettings.base_year}; minimal 0` },
                        ]}
                        note="Semua manfaat dan biaya proyek didiskontokan ke tahun dasar yang sama sebelum masuk TEV dan BCR."
                    />
                </>
            }
        >
            {/* Rendered outside the field grid: the method list is grouped by
                valuation approach, which the flat select option shape cannot
                express. */}
            <div className="form-group">
                <label className="form-label">Teknik Valuasi</label>
                <select value={data.method_used} onChange={(e) => setData('method_used', e.target.value)} className="form-input">
                    {VALUATION_TECHNIQUES.map((group) => (
                        <optgroup key={group.approach} label={group.approach}>
                            {group.techniques.map((t) => <option key={t.code} value={t.code}>{t.code} — {t.name}</option>)}
                        </optgroup>
                    ))}
                    <optgroup label="Lainnya">
                        {LEGACY_METHODS.map((m) => <option key={m} value={m}>{m}</option>)}
                    </optgroup>
                </select>
                {errors.method_used && <p className="form-error">{errors.method_used}</p>}
            </div>

            <FormField
                field={{ name: 'calculation_notes', label: 'Catatan Perhitungan', type: 'textarea', placeholder: 'Asumsi, sumber angka, atau catatan verifikasi' }}
                value={data.calculation_notes}
                onChange={(v) => setData('calculation_notes', v)}
                error={errors.calculation_notes}
            />
        </ModuleFormShell>
    );
}
