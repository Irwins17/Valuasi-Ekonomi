import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';

/**
 * Shared EOP form for both create and edit.
 *
 * The preview mirrors EopData's saving hook exactly: gross is ΔQ × price and
 * net subtracts the production cost, so a negative impact shows as a negative
 * figure rather than being hidden behind an absolute value.
 */
export default function EopForm({ project, eopData, serviceCategories }) {
    const isEdit = Boolean(eopData);
    const indexUrl = route('admin.modules.eop.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        commodity_name: eopData?.commodity_name || '',
        service_category: eopData?.service_category || 'provisioning',
        product_type: eopData?.product_type || '',
        production_before: eopData?.production_before ?? '',
        production_after: eopData?.production_after ?? '',
        unit: eopData?.unit || '',
        market_price: eopData?.market_price ?? '',
        production_cost: eopData?.production_cost ?? '',
        area_ha: eopData?.area_ha ?? '',
        period_year: eopData?.period_year ?? '',
        data_source: eopData?.data_source || '',
        impact_type: eopData?.impact_type || 'positive',
        notes: eopData?.notes || '',
    });

    const deltaQ = toNumber(data.production_after) - toNumber(data.production_before);
    const gross = deltaQ * toNumber(data.market_price);
    const net = gross - toNumber(data.production_cost);

    const fields = [
        { name: 'commodity_name', label: 'Nama Komoditas', type: 'text', required: true, placeholder: 'Contoh: Padi, Ikan, Kayu' },
        { name: 'service_category', label: 'Kategori Jasa', type: 'select', required: true, options: Object.entries(serviceCategories) },
        { name: 'product_type', label: 'Jenis Produk / Jasa', type: 'text', placeholder: 'Contoh: Hasil tangkapan, hasil panen' },
        { name: 'unit', label: 'Satuan', type: 'text', required: true, placeholder: 'Contoh: Ton, Kg, m³' },
        { name: 'production_before', label: 'Produksi Sebelum', type: 'number', required: true, placeholder: '0,00' },
        { name: 'production_after', label: 'Produksi Sesudah', type: 'number', required: true, placeholder: '0,00' },
        { name: 'market_price', label: 'Harga Pasar per Unit', type: 'currency', required: true, placeholder: 'Contoh: 50.000', prefix: 'Rp' },
        { name: 'production_cost', label: 'Biaya Produksi / Pengambilan', type: 'currency', placeholder: 'Contoh: 5.000.000', prefix: 'Rp', hint: 'Kosongkan bila hanya menghitung nilai kotor.' },
        { name: 'area_ha', label: 'Luas Area (ha)', type: 'number', placeholder: 'Contoh: 10,50', suffix: 'ha' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year' },
        {
            name: 'impact_type', label: 'Tipe Dampak', type: 'select', required: true,
            options: [['positive', 'Positif (Keuntungan)'], ['negative', 'Negatif (Kerugian)']],
            hint: 'Dampak positif otomatis dicatat sebagai manfaat proyek.',
        },
        { name: 'data_source', label: 'Sumber Data', type: 'text', placeholder: 'Contoh: Survei lapangan, data produksi, literatur' },
        { name: 'notes', label: 'Keterangan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Keterangan tambahan (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.eop.update', [project.id, eopData.id]));
        else post(route('admin.modules.eop.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data EOP`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data EOP"
            formTitle={`Form Input EOP — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula EOP"
                        formulas={[
                            'ΔQ = Q sesudah − Q sebelum',
                            'Nilai Kotor = ΔQ × Harga',
                            'Nilai Bersih = Nilai Kotor − Biaya Produksi',
                        ]}
                        legend={[
                            { sym: 'ΔQ', desc: 'Perubahan volume produksi' },
                            { sym: 'Harga', desc: 'Harga pasar per unit produk' },
                            { sym: 'Biaya', desc: 'Biaya produksi / pengambilan' },
                        ]}
                        note="Effect on Production menilai dampak lingkungan lewat perubahan produktivitas."
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Delta Produksi', sublabel: 'Q sesudah − Q sebelum', value: deltaQ, format: 'number', unit: data.unit || 'unit', color: deltaQ >= 0 ? '#6366f1' : '#ef4444' },
                            { label: 'Nilai Kotor', sublabel: 'ΔQ × Harga', value: gross, format: 'currency', unit: 'per periode', color: gross >= 0 ? '#f59e0b' : '#ef4444' },
                            { label: 'Nilai Bersih', sublabel: 'Nilai Kotor − Biaya', value: net, format: 'currency', unit: 'per periode', color: net >= 0 ? '#10b981' : '#ef4444' },
                        ]}
                        note="Dampak negatif menghasilkan nilai negatif dan tidak dicatat sebagai manfaat."
                    />
                </>
            }
        />
    );
}
