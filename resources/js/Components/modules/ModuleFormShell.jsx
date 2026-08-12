import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import FormField from './FormField';

export default function ModuleFormShell({
    title,
    projectName,
    backHref,
    backLabel,
    formTitle,
    fields,
    header,
    data,
    setData,
    errors,
    processing,
    onSubmit,
    sidebar,
    children,
    submitLabel = 'Simpan',
}) {
    return (
        <AdminLayout title={title} subtitle={<>Detail Proyek: <strong style={{ color: 'var(--primary)' }}>{projectName}</strong></>}>
            <Head title={title} />

            <div className="animate-fade-up">
                <Link href={backHref} style={{ color: 'var(--primary)', textDecoration: 'none', fontSize: 13, display: 'inline-block', marginBottom: 16 }}>
                    ← {backLabel}
                </Link>

                <div style={{ display: 'grid', gridTemplateColumns: 'minmax(0,1fr) 340px', gap: 16, alignItems: 'start' }}>
                    <div className="card">
                        <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>{formTitle}</h3>

                        <form onSubmit={onSubmit}>
                            {header}

                            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: '0 16px' }}>
                                {fields.map((field) => (
                                    <FormField
                                        key={field.name}
                                        field={field}
                                        value={data[field.name]}
                                        onChange={(v) => setData(field.name, v)}
                                        error={errors[field.name]}
                                    />
                                ))}
                            </div>

                            {children}

                            <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                                <button type="submit" className="btn btn-primary" disabled={processing}>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z" /><path d="M17 21v-8H7v8M7 3v5h8" /></svg>
                                    {processing ? 'Menyimpan…' : submitLabel}
                                </button>
                                <Link href={backHref} className="btn btn-ghost">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 6L6 18M6 6l12 12" /></svg>
                                    Batal
                                </Link>
                            </div>
                        </form>
                    </div>

                    <div>{sidebar}</div>
                </div>
            </div>
        </AdminLayout>
    );
}
