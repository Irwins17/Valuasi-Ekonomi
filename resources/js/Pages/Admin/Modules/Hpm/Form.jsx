import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';

export default function Form({ project, record, propertyTypes, scales }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.hpm.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        property_code: record?.property_code || '',
        transaction_price: record?.transaction_price ?? '',
        property_type: record?.property_type || '',
        location: record?.location || '',
        bedrooms: record?.bedrooms ?? '',
        land_area: record?.land_area ?? '',
        building_area: record?.building_area ?? '',
        building_age: record?.building_age ?? '',
        accessibility: record?.accessibility || '',
        crime_rate: record?.crime_rate || '',
        school_quality: record?.school_quality || '',
        air_quality_index: record?.air_quality_index ?? '',
        pollutant_concentration: record?.pollutant_concentration || '',
        noise_level: record?.noise_level ?? '',
        distance_green_space: record?.distance_green_space ?? '',
        delta_env_quality: record?.delta_env_quality ?? '',
        affected_units: record?.affected_units ?? '',
        data_source: record?.data_source || '',
        notes: record?.notes || '',
    });

    const fields = [
        { name: 'property_code', label: 'ID Properti / Unit', type: 'text', required: true, placeholder: 'Contoh: HPM-250801-001' },
        { name: 'transaction_price', label: 'Harga Transaksi / Sewa', type: 'currency', required: true, prefix: 'Rp', placeholder: 'Contoh: 1.250.000.000' },
        { name: 'property_type', label: 'Tipe Properti', type: 'select', required: true, options: propertyTypes, placeholder: 'Pilih tipe properti' },
        { name: 'location', label: 'Lokasi Properti', type: 'text', required: true, placeholder: 'Contoh: Kota / Kabupaten / Kecamatan' },
        { name: 'bedrooms', label: 'Jumlah Kamar', type: 'number', step: '1', placeholder: 'Contoh: 3' },
        { name: 'land_area', label: 'Luas Tanah (m²)', type: 'number', placeholder: 'Contoh: 120', suffix: 'm²' },
        { name: 'building_area', label: 'Luas Bangunan (m²)', type: 'number', placeholder: 'Contoh: 85', suffix: 'm²' },
        { name: 'building_age', label: 'Usia Bangunan (tahun)', type: 'number', step: '1', placeholder: 'Contoh: 8', suffix: 'tahun' },
        { name: 'accessibility', label: 'Aksesibilitas', type: 'select', options: scales, placeholder: 'Pilih aksesibilitas' },
        { name: 'crime_rate', label: 'Tingkat Kejahatan Lingkungan', type: 'select', options: scales, placeholder: 'Pilih tingkat kejahatan' },
        { name: 'school_quality', label: 'Kualitas Sekolah Sekitar', type: 'select', options: scales, placeholder: 'Pilih kualitas sekolah' },
        { name: 'air_quality_index', label: 'Indeks Kualitas Udara (AQI)', type: 'number', placeholder: 'Contoh: 56' },
        { name: 'pollutant_concentration', label: 'Konsentrasi Cemaran', type: 'select', options: scales, placeholder: 'Pilih tingkat cemaran' },
        { name: 'noise_level', label: 'Tingkat Kebisingan (dB)', type: 'number', placeholder: 'Contoh: 58', suffix: 'dB' },
        { name: 'distance_green_space', label: 'Jarak ke RTH / Pantai (km)', type: 'number', placeholder: 'Contoh: 2.5', suffix: 'km' },
        { name: 'delta_env_quality', label: 'Perubahan Kualitas Lingkungan (ΔE)', type: 'number', allowNegative: true, placeholder: 'Contoh: 0.15', hint: 'Dipakai menghitung nilai agregat.' },
        { name: 'affected_units', label: 'Jumlah Unit Terdampak (M)', type: 'number', step: '1', placeholder: 'Contoh: 120', suffix: 'unit' },
        { name: 'data_source', label: 'Sumber Data', type: 'text', placeholder: 'Contoh: Data transaksi BPN, survei agen properti' },
        { name: 'notes', label: 'Catatan (Opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan terkait properti / unit yang dianalisis (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.hpm.update', [project.id, record.id]));
        else post(route('admin.modules.hpm.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data HPM`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data HPM"
            formTitle={`Form Input HPM — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula HPM"
                        formulas={[
                            'Pₕ = f(S, N, E)',
                            'ln Pₕ = α₀ + βS + γN + δE + e',
                            'MWTP = ∂P/∂E = δ × Pₕ',
                            'Nilai Total = MWTP × ΔE × M',
                        ]}
                        legend={[
                            { sym: 'S', desc: 'atribut struktural' },
                            { sym: 'N', desc: 'atribut lingkungan pemukiman' },
                            { sym: 'E', desc: 'atribut lingkungan hidup' },
                            { sym: 'M', desc: 'jumlah unit terdampak' },
                        ]}
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Harga Implisit Marginal (δ)', sublabel: 'Dari regresi seluruh properti', value: 'Lihat halaman daftar', format: 'raw', color: '#6366f1' },
                            { label: 'MWTP Lingkungan', sublabel: 'δ × Pₕ', value: 'Lihat halaman daftar', format: 'raw', color: '#f59e0b' },
                            { label: 'Nilai Agregat', sublabel: 'MWTP × ΔE × M', value: 'Lihat halaman daftar', format: 'raw', color: '#10b981' },
                        ]}
                        note="δ adalah koefisien regresi lintas seluruh properti proyek, bukan hasil satu baris. Nilainya dihitung dan ditampilkan pada halaman daftar HPM setelah data cukup."
                    />
                </>
            }
        />
    );
}
