import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';

const RESPONDENT_CATEGORIES = ['Wisatawan lokal', 'Wisatawan domestik', 'Wisatawan mancanegara', 'Pelajar/Mahasiswa', 'Peneliti', 'Lainnya'];
const EDUCATION_LEVELS = ['Tidak sekolah', 'SD', 'SMP', 'SMA/SMK', 'D3', 'S1', 'S2', 'S3'];

export default function TcmForm({ project, tcmData }) {
    const isEdit = Boolean(tcmData);
    const indexUrl = route('admin.modules.tcm.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        respondent_id: tcmData?.respondent_id ?? '',
        origin_location: tcmData?.origin_location || '',
        respondent_category: tcmData?.respondent_category || '',
        visit_frequency: tcmData?.visit_frequency ?? '',
        distance: tcmData?.distance ?? '',
        transportation_cost: tcmData?.transportation_cost ?? '',
        ticket_cost: tcmData?.ticket_cost ?? '',
        travel_time_hours: tcmData?.travel_time_hours ?? '',
        time_value_per_hour: tcmData?.time_value_per_hour ?? '',
        income: tcmData?.income ?? '',
        age: tcmData?.age ?? '',
        education: tcmData?.education || '',
        substitute_site: tcmData?.substitute_site || '',
        notes: tcmData?.notes || '',
    });

    const timeCost = toNumber(data.travel_time_hours) * toNumber(data.time_value_per_hour);
    const totalTc = toNumber(data.transportation_cost) + toNumber(data.ticket_cost) + timeCost;
    const annualSpend = totalTc * toNumber(data.visit_frequency);

    const fields = [
        ...(isEdit ? [] : [{ name: 'respondent_id', label: 'ID Responden', type: 'number', required: true, step: '1', placeholder: 'Contoh: 101' }]),
        { name: 'origin_location', label: 'Asal Lokasi / Zona', type: 'text', placeholder: 'Contoh: Zona 1 — Kec. Watulimo' },
        { name: 'respondent_category', label: 'Kategori Responden', type: 'select', options: RESPONDENT_CATEGORIES, placeholder: '-- Pilih kategori --' },
        { name: 'visit_frequency', label: 'Frekuensi Kunjungan per Tahun', type: 'number', required: true, step: '1', placeholder: 'Contoh: 3', suffix: 'kali/tahun' },
        { name: 'distance', label: 'Jarak PP', type: 'number', required: true, placeholder: 'Contoh: 45,00', suffix: 'km' },
        { name: 'transportation_cost', label: 'Biaya Transport PP', type: 'currency', required: true, placeholder: 'Contoh: 150.000', prefix: 'Rp' },
        { name: 'ticket_cost', label: 'Biaya Tiket / Retribusi', type: 'currency', placeholder: 'Contoh: 15.000', prefix: 'Rp' },
        { name: 'travel_time_hours', label: 'Waktu Tempuh PP', type: 'number', placeholder: 'Contoh: 3,50', suffix: 'jam' },
        { name: 'time_value_per_hour', label: 'Nilai Waktu per Jam', type: 'currency', placeholder: 'Contoh: 20.000', prefix: 'Rp', hint: 'Biaya waktu dihitung otomatis: waktu tempuh × nilai waktu.' },
        { name: 'income', label: 'Pendapatan', type: 'currency', placeholder: 'Contoh: 4.500.000', prefix: 'Rp', hint: 'Per bulan.' },
        { name: 'age', label: 'Usia', type: 'number', step: '1', placeholder: 'Contoh: 32', suffix: 'tahun' },
        { name: 'education', label: 'Pendidikan', type: 'select', options: EDUCATION_LEVELS, placeholder: '-- Pilih pendidikan --' },
        { name: 'substitute_site', label: 'Situs Pengganti / Alternatif Wisata', type: 'text', full: true, placeholder: 'Contoh: Pantai Prigi, Pantai Klayar' },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.tcm.update', [project.id, tcmData.id]));
        else post(route('admin.modules.tcm.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Responden TCM`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data TCM"
            formTitle={`Form Responden TCM — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula Biaya Perjalanan"
                        formulas={'TC = biaya transport\n     + tiket/retribusi\n     + (nilai waktu × waktu tempuh)'}
                        legend={[
                            { sym: 'TC', desc: 'Total biaya perjalanan per kunjungan' },
                            { sym: 'Vij', desc: 'Frekuensi kunjungan responden per tahun' },
                        ]}
                        note="Surplus konsumen dan nilai rekreasi dihitung pada halaman Analisis TCM dari seluruh responden, bukan per baris ini."
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Biaya Waktu', sublabel: 'Waktu tempuh × nilai waktu', value: timeCost, format: 'currency', unit: 'per kunjungan', color: '#6366f1' },
                            { label: 'Total Biaya Perjalanan (TC)', sublabel: 'Transport + tiket + biaya waktu', value: totalTc, format: 'currency', unit: 'per kunjungan', color: '#10b981' },
                            { label: 'Pengeluaran per Tahun', sublabel: 'TC × frekuensi kunjungan', value: annualSpend, format: 'currency', unit: 'per tahun', color: '#f59e0b' },
                        ]}
                        note="“Pengeluaran per tahun” adalah belanja perjalanan, bukan surplus konsumen."
                    />
                </>
            }
        />
    );
}
