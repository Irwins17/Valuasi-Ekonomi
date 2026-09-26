import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';
import { DATA_COLLECTION_TYPES, collectionMethodsFor } from '../../../../lib/dataCollectionTypes';

const CHOSEN_LABELS = { a: 'Alternatif A', b: 'Alternatif B', status_quo: 'Status Quo' };

export default function Form({ project, record, educationLevels }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.ce.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        respondent_code: record?.respondent_code || '',
        location: record?.location || '',
        age: record?.age ?? '',
        education: record?.education || '',
        income: record?.income ?? '',
        scenario_title: record?.scenario_title || '',
        choice_set: record?.choice_set || '',
        alternative_a: record?.alternative_a || '',
        alternative_b: record?.alternative_b || '',
        status_quo: record?.status_quo || '',
        chosen_alternative: record?.chosen_alternative || 'status_quo',
        attribute_1: record?.attribute_1 || '',
        attribute_1_level: record?.attribute_1_level || '',
        attribute_2: record?.attribute_2 || '',
        attribute_2_level: record?.attribute_2_level || '',
        attribute_3: record?.attribute_3 || '',
        attribute_3_level: record?.attribute_3_level || '',
        cost_attribute: record?.cost_attribute ?? '',
        data_source: record?.data_source || '',
        data_collection_type: record?.data_collection_type || '',
        collection_method: record?.collection_method || '',
        notes: record?.notes || '',
    });

    const fields = [
        // Identitas responden
        { name: 'respondent_code', label: 'ID Responden', type: 'text', required: true, placeholder: 'Contoh: CE-0015' },
        { name: 'location', label: 'Lokasi', type: 'text', placeholder: 'Contoh: Desa Tasikmadu' },
        { name: 'age', label: 'Usia', type: 'number', step: '1', placeholder: 'Contoh: 34', suffix: 'tahun' },
        { name: 'education', label: 'Pendidikan', type: 'select', options: educationLevels, placeholder: '-- Pilih pendidikan --' },
        { name: 'income', label: 'Pendapatan', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 4.500.000', hint: 'Per bulan.' },

        // Skenario pilihan
        { name: 'scenario_title', label: 'Judul Skenario', type: 'text', required: true, full: true, placeholder: 'Contoh: Pengelolaan kawasan konservasi mangrove' },
        { name: 'choice_set', label: 'Choice Set', type: 'text', required: true, placeholder: 'Contoh: CS-01' },
        { name: 'chosen_alternative', label: 'Alternatif Dipilih', type: 'select', required: true, options: Object.entries(CHOSEN_LABELS) },
        { name: 'alternative_a', label: 'Alternatif A', type: 'text', placeholder: 'Ringkasan atribut alternatif A' },
        { name: 'alternative_b', label: 'Alternatif B', type: 'text', placeholder: 'Ringkasan atribut alternatif B' },
        { name: 'status_quo', label: 'Status Quo', type: 'text', full: true, placeholder: 'Kondisi tanpa perubahan kebijakan' },

        // Atribut & biaya
        { name: 'attribute_1', label: 'Atribut 1', type: 'text', placeholder: 'Contoh: Luas mangrove direhabilitasi' },
        { name: 'attribute_1_level', label: 'Level Atribut 1', type: 'text', placeholder: 'Contoh: 50 ha' },
        { name: 'attribute_2', label: 'Atribut 2', type: 'text', placeholder: 'Contoh: Kualitas air' },
        { name: 'attribute_2_level', label: 'Level Atribut 2', type: 'text', placeholder: 'Contoh: Baik' },
        { name: 'attribute_3', label: 'Atribut 3', type: 'text', placeholder: 'Contoh: Akses publik' },
        { name: 'attribute_3_level', label: 'Level Atribut 3', type: 'text', placeholder: 'Contoh: Terbatas' },
        { name: 'cost_attribute', label: 'Biaya / Pajak / Retribusi', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 25.000', hint: 'Atribut moneter — penyebut βp pada MWTP.' },

        { name: 'data_source', label: 'Sumber Data', type: 'text', placeholder: 'Contoh: Survei choice experiment 2026' },
        { name: 'data_collection_type', label: 'Jenis Data', type: 'select', options: DATA_COLLECTION_TYPES.map((t) => [t.value, t.label]), placeholder: '-- Pilih jenis data --' },
        { name: 'collection_method', label: 'Metode Pengumpulan', type: 'select', options: collectionMethodsFor(data.data_collection_type).map((m) => [m.value, m.label]), placeholder: '-- Pilih metode --' },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.ce.update', [project.id, record.id]));
        else post(route('admin.modules.ce.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Choice Experiment`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data CE"
            formTitle={`Form Input Choice Experiment — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula Choice Experiment"
                        formulas={[
                            'Uᵢⱼₜ = Vᵢⱼₜ + εᵢⱼₜ',
                            'Pᵢⱼₜ = exp(Vᵢⱼₜ) / Σ exp(Vᵢₘₜ)',
                            'MWTPₖ = −βₖ / βₚ',
                            'CV = −(1/βₚ)[ln Σe^V¹ − ln Σe^V⁰]',
                        ]}
                        legend={[
                            { sym: 'βₖ', desc: 'koefisien atribut non-moneter ke-k' },
                            { sym: 'βₚ', desc: 'koefisien atribut biaya' },
                            { sym: 'CV', desc: 'compensating variation' },
                        ]}
                        note="Koefisien β diperoleh dari estimasi conditional logit di perangkat lunak statistik; sistem ini menyimpan desain dan jawaban responden."
                    />
                    <OutputPreview
                        title="Ringkasan Baris Ini"
                        rows={[
                            { label: 'Pilihan Responden', sublabel: `Choice set ${data.choice_set || '—'}`, value: CHOSEN_LABELS[data.chosen_alternative] || '—', format: 'raw', color: '#6366f1' },
                            { label: 'Atribut Biaya', sublabel: 'Penyebut βp pada MWTP', value: toNumber(data.cost_attribute), format: 'currency', color: '#f59e0b' },
                            { label: 'Marginal WTP', sublabel: '−βk / βp', value: 'Perlu estimasi β', format: 'raw', color: '#94a3b8' },
                        ]}
                        note="MWTP dan Compensating Variation memerlukan estimasi conditional logit atas seluruh choice set, sehingga tidak dihitung dari satu baris."
                    />
                </>
            }
        />
    );
}
