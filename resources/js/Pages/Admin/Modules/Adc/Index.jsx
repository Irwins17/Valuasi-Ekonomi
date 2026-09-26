import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

const STATUS_BADGE = { draft: 'badge-warning', verified: 'badge-info', final: 'badge-success' };

export default function Index({ project, records, totals, statuses }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    return (
        <ModuleIndexShell
            title="Data Avoided Damage Cost"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.adc.create', project.id)}
            createLabel="Tambah Data"
            canManage={canManage}
            cardTitle="Data Avoided Damage Cost"
            cardSubtitle="ADC = Luas Terlindungi × Biaya Kerusakan per Unit × Probabilitas Kejadian"
            isEmpty={!records.data.length}
            emptyText="Belum ada data Avoided Damage Cost."
            stats={[
                { label: 'Jumlah Data', value: totals.records, unit: 'record', color: 'var(--primary)' },
                { label: 'Total Kerusakan Dihindari', value: totals.avoided, format: 'currency', color: 'var(--success)' },
            ]}
        >
            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Data</th>
                            <th>Jenis Kerusakan</th>
                            <th>Lokasi</th>
                            <th style={{ textAlign: 'right' }}>Luas Terlindungi</th>
                            <th style={{ textAlign: 'right' }}>Biaya Kerusakan/Unit</th>
                            <th style={{ textAlign: 'right' }}>Probabilitas</th>
                            <th style={{ textAlign: 'right' }}>Nilai Dihindari</th>
                            <th>Status</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.record_code}</td>
                                <td style={{ fontSize: 13 }}>{r.damage_type}</td>
                                <td style={{ fontSize: 13 }}>{r.location}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(r.protected_area, 2)} ha</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(r.damage_cost_per_unit, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(r.event_probability * 100, 1)}%</td>
                                <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700, color: 'var(--success)' }}>Rp{formatRupiah(r.avoided_cost, 0)}</td>
                                <td><span className={`badge ${STATUS_BADGE[r.data_status] || 'badge-gray'}`}>{statuses[r.data_status]}</span></td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.adc.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.adc.destroy', [project.id, r.id])} message={`Hapus data ${r.record_code}?`} />
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
