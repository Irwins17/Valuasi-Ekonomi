import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../Components/modules/ModuleFormShell';

export default function Form({ project, record, statuses }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.assumptions.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        assumption_type: record?.assumption_type || '',
        assumed_value: record?.assumed_value || '',
        justification: record?.justification || '',
        tested_result: record?.tested_result || '',
        status: record?.status || 'valid',
    });

    const fields = [
        { name: 'assumption_type', label: 'Jenis Asumsi', type: 'text', required: true, placeholder: 'Contoh: Discount Rate, Elastisitas Harga, Umur Manfaat Ekosistem' },
        { name: 'assumed_value', label: 'Nilai yang Diasumsikan', type: 'text', required: true, placeholder: 'Contoh: 10%, 20 tahun' },
        { name: 'status', label: 'Status', type: 'select', required: true, options: Object.entries(statuses) },
        { name: 'justification', label: 'Justifikasi', type: 'textarea', required: true, full: true, placeholder: 'Dasar/alasan asumsi ini dipakai' },
        { name: 'tested_result', label: 'Hasil Pengujian (opsional)', type: 'textarea', full: true, placeholder: 'Hasil analisis kepekaan terhadap asumsi ini, bila sudah diuji' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.assumptions.update', [project.id, record.id]));
        else post(route('admin.assumptions.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Asumsi`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Uji Asumsi"
            formTitle={`Form Asumsi Valuasi — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
        />
    );
}
