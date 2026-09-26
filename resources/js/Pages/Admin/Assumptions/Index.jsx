import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../Components/ui/ConfirmDeleteButton';

const STATUS_BADGE = { valid: 'badge-success', perlu_revisi: 'badge-warning', tidak_valid: 'badge-danger' };

export default function Index({ project, records, statuses }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isAnalyst;

    return (
        <ModuleIndexShell
            title="Uji Asumsi"
            projectName={project.name}
            backHref={route('admin.projects.show', project.id)}
            backLabel="Kembali ke Proyek"
            createHref={route('admin.assumptions.create', project.id)}
            createLabel="Tambah Asumsi"
            canManage={canManage}
            cardTitle="Daftar Asumsi Valuasi"
            cardSubtitle="Langkah 9 — dokumentasi & pengujian asumsi kunci di balik perhitungan TEV proyek ini."
            isEmpty={!records.data.length}
            emptyText="Belum ada asumsi yang didokumentasikan."
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>Jenis Asumsi</th>
                            <th>Nilai Diasumsikan</th>
                            <th>Justifikasi</th>
                            <th>Hasil Pengujian</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.assumption_type}</td>
                                <td style={{ fontSize: 13 }}>{r.assumed_value}</td>
                                <td style={{ fontSize: 13, maxWidth: 280 }}>{r.justification}</td>
                                <td style={{ fontSize: 13, maxWidth: 280 }}>{r.tested_result || '—'}</td>
                                <td><span className={`badge ${STATUS_BADGE[r.status] || 'badge-gray'}`}>{statuses[r.status]}</span></td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.assumptions.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.assumptions.destroy', [project.id, r.id])} message={`Hapus asumsi ${r.assumption_type}?`} />
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
