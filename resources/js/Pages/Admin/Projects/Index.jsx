import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import Pagination from '../../../Components/ui/Pagination';
import { formatTriliun, formatRupiah } from '../../../lib/format';

const STATUS_BADGE = {
    published: 'badge-success',
    in_progress: 'badge-warning',
    completed: 'badge-info',
    draft: 'badge-gray',
};

export default function Index({ projects }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin;

    return (
        <AdminLayout title="Manajemen Proyek" subtitle="Kelola semua proyek valuasi ekonomi">
            <Head title="Manajemen Proyek" />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }} className="animate-fade-up">
                <div></div>
                {canManage && <Link href={route('admin.projects.create')} className="btn btn-sm btn-primary">+ Proyek Baru</Link>}
            </div>

            <div className="card">
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Proyek</th>
                                <th>Lokasi</th>
                                <th>Status</th>
                                <th>TEV</th>
                                <th>BCR</th>
                                <th style={{ textAlign: 'center' }}>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            {projects.data.length ? (
                                projects.data.map((project) => (
                                    <tr key={project.id}>
                                        <td><span style={{ fontFamily: 'monospace', fontSize: 13, color: 'var(--text-muted)' }}>{project.code}</span></td>
                                        <td>
                                            <div>
                                                <span style={{ fontWeight: 600 }}>{project.name}</span>
                                                <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>
                                                    {(project.description || '').length > 40 ? project.description.slice(0, 40) + '…' : project.description}
                                                </p>
                                            </div>
                                        </td>
                                        <td style={{ fontSize: 13, color: 'var(--text-secondary)' }}>{project.location}</td>
                                        <td>
                                            <span className={`badge ${STATUS_BADGE[project.status] || 'badge-gray'}`}>
                                                {project.status.replace('_', ' ').replace(/^./, (c) => c.toUpperCase())}
                                            </span>
                                        </td>
                                        <td style={{ fontWeight: 700, color: 'var(--primary)' }}>
                                            {project.tev ? `Rp${formatTriliun(project.tev)}T` : '-'}
                                        </td>
                                        <td>
                                            <span style={{ fontWeight: 700, color: (project.bcr ?? 0) >= 1 ? 'var(--success)' : 'var(--danger)' }}>
                                                {project.bcr ? formatRupiah(project.bcr, 2) : '-'}
                                            </span>
                                        </td>
                                        <td style={{ textAlign: 'center' }}>
                                            <div style={{ display: 'flex', gap: 4, justifyContent: 'center' }}>
                                                <Link href={route('admin.projects.show', project.id)} className="btn btn-sm btn-ghost">Lihat</Link>
                                                {canManage && <Link href={route('admin.projects.edit', project.id)} className="btn btn-sm btn-ghost">Edit</Link>}
                                            </div>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan={7} style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>
                                        <p style={{ marginBottom: 8 }}>Belum ada proyek</p>
                                        {canManage && <Link href={route('admin.projects.create')} style={{ color: 'var(--primary)', fontWeight: 600, textDecoration: 'none' }}>Buat proyek pertama →</Link>}
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <Pagination paginator={projects} />
            </div>
        </AdminLayout>
    );
}
