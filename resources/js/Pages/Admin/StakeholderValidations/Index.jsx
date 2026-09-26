import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../Components/ui/ConfirmDeleteButton';

const STATUS_BADGE = { pending: 'badge-warning', disetujui: 'badge-success', perlu_revisi: 'badge-danger' };

export default function Index({ project, records, statuses }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isAnalyst;

    return (
        <ModuleIndexShell
            title="Validasi Stakeholder"
            projectName={project.name}
            backHref={route('admin.projects.show', project.id)}
            backLabel="Kembali ke Proyek"
            createHref={route('admin.stakeholder-validations.create', project.id)}
            createLabel="Tambah Validasi"
            canManage={canManage}
            cardTitle="Daftar Validasi Stakeholder"
            cardSubtitle="Langkah 9 — umpan balik pemangku kepentingan terhadap hasil valuasi proyek ini."
            isEmpty={!records.data.length}
            emptyText="Belum ada validasi stakeholder yang tercatat."
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>Stakeholder</th>
                            <th>Jabatan / Instansi</th>
                            <th>Tanggal</th>
                            <th>Feedback</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.stakeholder_name}</td>
                                <td style={{ fontSize: 13 }}>{r.stakeholder_role || '—'}</td>
                                <td style={{ fontSize: 13 }}>{r.validation_date}</td>
                                <td style={{ fontSize: 13, maxWidth: 280 }}>{r.feedback || '—'}</td>
                                <td><span className={`badge ${STATUS_BADGE[r.status] || 'badge-gray'}`}>{statuses[r.status]}</span></td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.stakeholder-validations.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.stakeholder-validations.destroy', [project.id, r.id])} message={`Hapus validasi dari ${r.stakeholder_name}?`} />
                                        </div>
                                    </td>
                                )}
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>
            <Pagination paginator={records} />
        </ModuleIndexShell>
    );
}
