import { Link, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

const CHOSEN_LABELS = { a: 'Alternatif A', b: 'Alternatif B', status_quo: 'Status Quo' };
const CHOSEN_BADGE = { a: 'badge-primary', b: 'badge-info', status_quo: 'badge-gray' };

export default function Index({ project, records, totals, choiceShares }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    const totalChoices = Object.values(choiceShares || {}).reduce((s, v) => s + Number(v), 0);

    return (
        <ModuleIndexShell
            title="Data Choice Experiment"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.ce.create', project.id)}
            createLabel="Tambah Data CE"
            canManage={canManage}
            cardTitle="Jawaban Choice Set"
            cardSubtitle="Uᵢⱼₜ = Vᵢⱼₜ + εᵢⱼₜ · MWTPₖ = −βₖ/βₚ"
            isEmpty={!records.data.length}
            emptyText="Belum ada data Choice Experiment."
            stats={[
                { label: 'Jumlah Jawaban', value: totals.records, unit: 'baris', color: 'var(--primary)' },
                { label: 'Jumlah Responden', value: totals.respondents, unit: 'orang', color: 'var(--info)' },
                { label: 'Jumlah Choice Set', value: totals.choice_sets, unit: 'set', color: 'var(--warning)' },
            ]}
        >
            {totalChoices > 0 && (
                <div className="card" style={{ background: 'var(--surface-alt)', marginBottom: 16 }}>
                    <h4 style={{ fontSize: 13.5, fontWeight: 700, marginBottom: 10 }}>Distribusi Pilihan</h4>
                    <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(140px, 1fr))', gap: 12 }}>
                        {Object.entries(CHOSEN_LABELS).map(([key, label]) => {
                            const count = Number(choiceShares?.[key] || 0);
                            return (
                                <div key={key}>
                                    <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{label}</div>
                                    <div style={{ fontSize: 16, fontWeight: 800 }}>{count}</div>
                                    <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>
                                        {formatRupiah((count / totalChoices) * 100, 1)}%
                                    </div>
                                </div>
                            );
                        })}
                    </div>
                    <p style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 10, lineHeight: 1.55 }}>
                        Pangsa pilihan ini bersifat deskriptif. Marginal WTP dan Compensating Variation memerlukan
                        estimasi conditional logit atas seluruh desain, yang dilakukan di luar sistem ini.
                    </p>
                </div>
            )}

            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Responden</th>
                            <th>Choice Set</th>
                            <th>Skenario</th>
                            <th>Dipilih</th>
                            <th>Atribut 1</th>
                            <th>Atribut 2</th>
                            <th style={{ textAlign: 'right' }}>Biaya</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.respondent_code}</td>
                                <td style={{ fontSize: 13 }}>{r.choice_set}</td>
                                <td style={{ fontSize: 13 }}>{(r.scenario_title || '').length > 32 ? `${r.scenario_title.slice(0, 32)}…` : r.scenario_title}</td>
                                <td><span className={`badge ${CHOSEN_BADGE[r.chosen_alternative]}`}>{CHOSEN_LABELS[r.chosen_alternative]}</span></td>
                                <td style={{ fontSize: 12 }}>{r.attribute_1 ? `${r.attribute_1}: ${r.attribute_1_level || '-'}` : '-'}</td>
                                <td style={{ fontSize: 12 }}>{r.attribute_2 ? `${r.attribute_2}: ${r.attribute_2_level || '-'}` : '-'}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.cost_attribute ? `Rp${formatRupiah(r.cost_attribute, 0)}` : '-'}</td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.ce.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.ce.destroy', [project.id, r.id])} message={`Hapus jawaban ${r.respondent_code} pada ${r.choice_set}?`} />
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
