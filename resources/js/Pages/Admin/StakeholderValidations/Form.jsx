import { useForm } from '@inertiajs/react';
import ModuleFormShell from '../../../Components/modules/ModuleFormShell';

export default function Form({ project, record, statuses }) {
    const isEdit = Boolean(record);
    const indexUrl = route('admin.stakeholder-validations.index', project.id);

    const { data, setData, post, put, processing, errors } = useForm({
        stakeholder_name: record?.stakeholder_name || '',
        stakeholder_role: record?.stakeholder_role || '',
        validation_date: record?.validation_date || '',
        feedback: record?.feedback || '',
        status: record?.status || 'pending',
    });

    const fields = [
        { name: 'stakeholder_name', label: 'Nama Stakeholder', type: 'text', required: true, placeholder: 'Contoh: Ketua Kelompok Nelayan Desa X' },
        { name: 'stakeholder_role', label: 'Jabatan / Instansi', type: 'text', placeholder: 'Contoh: Dinas Kelautan dan Perikanan' },
        { name: 'validation_date', label: 'Tanggal Validasi', type: 'date', required: true },
        { name: 'status', label: 'Status', type: 'select', required: true, options: Object.entries(statuses) },
        { name: 'feedback', label: 'Feedback / Catatan', type: 'textarea', full: true, placeholder: 'Umpan balik atau catatan dari stakeholder' },
    ];

    function submit(e) {
        e.preventDefault();
        if (isEdit) put(route('admin.stakeholder-validations.update', [project.id, record.id]));
        else post(route('admin.stakeholder-validations.store', project.id));
    }

    return (
        <ModuleFormShell
            title={`${isEdit ? 'Edit' : 'Tambah'} Validasi Stakeholder`}
            projectName={project.name}
            backHref={indexUrl}
            backLabel="Kembali ke Validasi Stakeholder"
            formTitle={`Form Validasi Stakeholder — ${project.name}`}
            fields={fields}
            data={data}
            setData={setData}
            errors={errors}
            processing={processing}
            onSubmit={submit}
        />
    );
}
