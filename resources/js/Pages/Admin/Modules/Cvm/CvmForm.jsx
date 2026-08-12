import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';

const EDUCATION_LEVELS = ['Tidak sekolah', 'SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2', 'S3'];
const OCCUPATIONS = ['Petani', 'Nelayan', 'Pedagang', 'Wiraswasta', 'PNS/ASN', 'Karyawan swasta', 'Pelajar/Mahasiswa', 'Ibu rumah tangga', 'Lainnya'];

export default function CvmForm({ project, cvmData }) {
    const isEdit = Boolean(cvmData);
    const indexUrl = route('admin.modules.cvm.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        respondent_id: cvmData?.respondent_id ?? '',
        respondent_location: cvmData?.respondent_location || '',
        scenario: cvmData?.scenario || '',
        valuation_type: cvmData?.valuation_type || 'wtp',
        question_method: cvmData?.question_method || 'open_ended',
        bid_amount: cvmData?.bid_amount ?? '',
        willing_to_pay: cvmData ? Boolean(cvmData.willing_to_pay) : true,
        wtp: cvmData?.wtp ?? '',
        household_size: cvmData?.household_size ?? '',
        household_income: cvmData?.household_income ?? '',
        age: cvmData?.age ?? '',
        education_level: cvmData?.education_level || '',
        occupation: cvmData?.occupation || '',
        reason_if_unwilling: cvmData?.reason_if_unwilling || '',
        notes: cvmData?.notes || '',
    });

    const isDichotomous = data.question_method === 'dichotomous_choice';
    const wtpLabel = data.valuation_type === 'wta' ? 'WTA' : 'WTP';

    const fields = [
        ...(isEdit ? [] : [{ name: 'respondent_id', label: 'ID Responden', type: 'number', required: true, step: '1', placeholder: 'Contoh: 101' }]),
        { name: 'respondent_location', label: 'Lokasi Responden', type: 'text', placeholder: 'Contoh: Desa Tasikmadu' },
        { name: 'scenario', label: 'Skenario / Program Lingkungan', type: 'text', full: true, placeholder: 'Contoh: Program rehabilitasi mangrove 100 ha' },
        { name: 'valuation_type', label: 'Tipe Penilaian', type: 'select', required: true, options: [['wtp', 'WTP — Willingness To Pay'], ['wta', 'WTA — Willingness To Accept']] },
        { name: 'question_method', label: 'Metode Pertanyaan', type: 'select', required: true, options: [['dichotomous_choice', 'Dichotomous Choice'], ['open_ended', 'Open Ended'], ['payment_card', 'Payment Card']] },
        {
            name: 'bid_amount', label: 'Nilai Tawaran / Bid Amount', type: 'currency', prefix: 'Rp',
            required: isDichotomous, placeholder: 'Contoh: 50.000',
            hint: isDichotomous
                ? 'Wajib untuk dichotomous choice — analisis logit/probit membutuhkannya.'
                : 'Hanya relevan untuk metode dichotomous choice.',
        },
        { name: 'willing_to_pay', label: `Bersedia membayar (${wtpLabel})?`, type: 'checkbox', full: true, hint: 'Centang bila responden menjawab "Ya".' },
        {
            name: 'wtp', label: `Nilai ${wtpLabel} Aktual`, type: 'currency', prefix: 'Rp',
            required: data.willing_to_pay, placeholder: 'Contoh: 35.000',
            hint: data.willing_to_pay ? 'Nilai yang benar-benar dinyatakan responden.' : 'Kosongkan bila responden tidak bersedia.',
        },
        { name: 'household_size', label: 'Jumlah Anggota RT', type: 'number', step: '1', placeholder: 'Contoh: 4', suffix: 'orang' },
        { name: 'household_income', label: 'Pendapatan RT', type: 'currency', prefix: 'Rp', placeholder: 'Contoh: 4.500.000', hint: 'Per bulan.' },
        { name: 'age', label: 'Usia', type: 'number', step: '1', placeholder: 'Contoh: 38', suffix: 'tahun' },
        { name: 'education_level', label: 'Pendidikan', type: 'select', options: EDUCATION_LEVELS, placeholder: '-- Pilih pendidikan --' },
        { name: 'occupation', label: 'Pekerjaan / Kategori Responden', type: 'select', options: OCCUPATIONS, placeholder: '-- Pilih pekerjaan --' },
        ...(data.willing_to_pay ? [] : [{ name: 'reason_if_unwilling', label: 'Alasan Tidak Bersedia', type: 'textarea', full: true, required: true, maxLength: 500, placeholder: 'Contoh: Sudah membayar retribusi lain' }]),
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.cvm.update', [project.id, cvmData.id]));
        else post(route('admin.modules.cvm.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Responden CVM`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data CVM"
            formTitle={`Form Responden CVM — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula CVM"
                        formulas={['Mean WTP = ΣWTP / n', 'Total WTP = Mean WTP × Populasi']}
                        legend={[
                            { sym: 'WTP', desc: 'Nilai kesediaan membayar responden' },
                            { sym: 'n', desc: 'Jumlah responden' },
                            { sym: 'Ai', desc: 'Nilai tawaran (dichotomous choice)' },
                        ]}
                        note="Rumus di atas berlaku untuk open-ended. Dichotomous choice diolah dengan model logit/probit pada halaman Analisis CVM."
                    />
                    <OutputPreview
                        title="Ringkasan Baris Ini"
                        rows={[
                            { label: 'Tipe Penilaian', sublabel: 'WTP atau WTA', value: wtpLabel, format: 'raw', color: '#6366f1' },
                            { label: `Nilai ${wtpLabel}`, sublabel: data.willing_to_pay ? 'Dinyatakan responden' : 'Responden menolak', value: data.willing_to_pay ? toNumber(data.wtp) : 0, format: 'currency', color: data.willing_to_pay ? '#10b981' : '#ef4444' },
                            ...(isDichotomous ? [{ label: 'Nilai Tawaran (Ai)', sublabel: 'Dipakai model logit/probit', value: toNumber(data.bid_amount), format: 'currency', color: '#f59e0b' }] : []),
                        ]}
                        note="Mean dan median WTP seluruh responden ditampilkan pada halaman daftar CVM."
                    />
                </>
            }
        />
    );
}
