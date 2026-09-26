import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../../Components/modules/ModuleFormShell';
import FormulaPanel from '../../../../Components/modules/FormulaPanel';
import OutputPreview from '../../../../Components/modules/OutputPreview';
import { toNumber } from '../../../../lib/format';
import { DATA_COLLECTION_TYPES, collectionMethodsFor } from '../../../../lib/dataCollectionTypes';

export default function Form({ project, record, statuses, serviceCategories }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.modules.duv.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        record_code: record?.record_code || '',
        service_category: record?.service_category || 'provisioning',
        goods_type: record?.goods_type || '',
        location: record?.location || '',
        quantity: record?.quantity ?? '',
        unit: record?.unit || '',
        market_price: record?.market_price ?? '',
        production_cost: record?.production_cost ?? '',
        period_year: record?.period_year ?? '',
        data_source: record?.data_source || '',
        data_collection_type: record?.data_collection_type || '',
        collection_method: record?.collection_method || '',
        data_status: record?.data_status || 'draft',
        notes: record?.notes || '',
    });

    const gross = toNumber(data.quantity) * toNumber(data.market_price);
    const cost = toNumber(data.production_cost);

    const fields = [
        { name: 'record_code', label: 'ID Data', type: 'text', required: true, placeholder: 'Contoh: DUV-0015' },
        { name: 'service_category', label: 'Kategori Jasa', type: 'select', required: true, options: Object.entries(serviceCategories) },
        { name: 'goods_type', label: 'Jenis Barang / Jasa', type: 'text', required: true, placeholder: 'Contoh: Ikan tangkap, kayu bakar, air bersih' },
        { name: 'location', label: 'Lokasi / Ekosistem', type: 'text', required: true, placeholder: 'Contoh: Tambak, Mangrove, Sungai' },
        { name: 'quantity', label: 'Kuantitas per Periode (Qi)', type: 'number', required: true, placeholder: 'Contoh: 1.250', suffix: 'unit/periode' },
        { name: 'unit', label: 'Satuan', type: 'text', required: true, placeholder: 'Contoh: kg, ton, m³, ekor' },
        { name: 'market_price', label: 'Harga Pasar per Unit (Pi)', type: 'currency', required: true, placeholder: 'Contoh: 25.000', prefix: 'Rp' },
        { name: 'production_cost', label: 'Biaya Produksi / Pengambilan (Ci)', type: 'currency', placeholder: 'Contoh: 5.000.000', prefix: 'Rp', hint: 'Kosongkan bila hanya menghitung DUV gross.' },
        { name: 'period_year', label: 'Periode / Tahun', type: 'year', required: true },
        { name: 'data_source', label: 'Sumber Data', type: 'text', required: true, placeholder: 'Contoh: Survei lapangan, data produksi, literatur' },
        { name: 'data_collection_type', label: 'Jenis Data', type: 'select', options: DATA_COLLECTION_TYPES.map((t) => [t.value, t.label]), placeholder: '-- Pilih jenis data --' },
        { name: 'collection_method', label: 'Metode Pengumpulan', type: 'select', options: collectionMethodsFor(data.data_collection_type).map((m) => [m.value, m.label]), placeholder: '-- Pilih metode --' },
        { name: 'data_status', label: 'Status Data', type: 'select', required: true, options: Object.entries(statuses) },
        { name: 'notes', label: 'Catatan (opsional)', type: 'textarea', full: true, maxLength: 500, placeholder: 'Catatan tambahan (opsional)' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.modules.duv.update', [project.id, record.id]));
        else post(route('admin.modules.duv.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Data Nilai Pasar (Market Price)`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Data Nilai Pasar"
            formTitle={`Form Input Nilai Pasar (Market Price) — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
            sidebar={
                <>
                    <FormulaPanel
                        title="Formula Metode Nilai Pasar (Market Price)"
                        formulas={['Nilai kotor = Σ(Qi × Pi)', 'Nilai bersih = Σ(Qi × Pi) − Ci']}
                        legend={[
                            { sym: 'Qi', desc: 'Kuantitas barang/jasa ke-i per periode' },
                            { sym: 'Pi', desc: 'Harga pasar per unit barang/jasa ke-i' },
                            { sym: 'Ci', desc: 'Biaya produksi/pengambilan barang/jasa ke-i' },
                        ]}
                        note="Metode ini mengisi kategori nilai TEV 'Direct Use Value'. Nilai proyek adalah penjumlahan seluruh baris yang tercatat."
                    />
                    <OutputPreview
                        rows={[
                            { label: 'Gross DUV', sublabel: 'Qi × Pi', value: gross, format: 'currency', unit: 'per periode', color: '#6366f1' },
                            { label: 'Total Biaya', sublabel: 'Ci', value: cost, format: 'currency', unit: 'per periode', color: '#f59e0b' },
                            { label: 'Net DUV', sublabel: 'Gross − Ci', value: gross - cost, format: 'currency', unit: 'per periode', color: gross - cost >= 0 ? '#10b981' : '#ef4444' },
                        ]}
                        note="Nilai baris ini dijumlahkan menjadi total DUV proyek pada halaman daftar."
                    />
                </>
            }
        />
    );
}
