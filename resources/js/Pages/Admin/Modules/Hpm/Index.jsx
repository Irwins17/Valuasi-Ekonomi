import { Link, router, usePage } from '@inertiajs/react';
import ModuleIndexShell from '../../../../Components/modules/ModuleIndexShell';
import Pagination from '../../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../../Components/ui/ConfirmDeleteButton';
import { formatRupiah } from '../../../../lib/format';

export default function Index({ project, records, totals, estimation, environmentVariables, selectedEnv, minProperties }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin || auth.user.isSurveyor;

    function changeEnv(value) {
        router.get(route('admin.modules.hpm.index', project.id), { env: value }, { preserveScroll: true, preserveState: true });
    }

    return (
        <ModuleIndexShell
            title="Data HPM (Hedonic Pricing)"
            projectName={project.name}
            backHref={route('admin.modules.index', project.id)}
            backLabel="Kembali ke Modul Valuasi"
            createHref={route('admin.modules.hpm.create', project.id)}
            createLabel="Tambah Data HPM"
            canManage={canManage}
            cardTitle="Data Properti"
            cardSubtitle="ln Pₕ = α₀ + βS + γN + δE + e"
            isEmpty={!records.data.length}
            emptyText="Belum ada data properti HPM."
            stats={[
                { label: 'Jumlah Properti', value: totals.records, unit: 'record', color: 'var(--primary)' },
                { label: 'Rata-rata Harga', value: totals.mean_price, format: 'currency', unit: 'Pₕ', color: 'var(--info)' },
            ]}
            headerExtra={
                <select className="form-input" value={selectedEnv} onChange={(e) => changeEnv(e.target.value)} style={{ width: 'auto', fontSize: 13, padding: '6px 30px 6px 10px' }}>
                    {Object.entries(environmentVariables).map(([key, label]) => (
                        <option key={key} value={key}>E: {label}</option>
                    ))}
                </select>
            }
        >
            <div className="card" style={{ background: 'var(--surface-alt)', marginBottom: 16 }}>
                <h4 style={{ fontSize: 13.5, fontWeight: 700, marginBottom: 10 }}>
                    Regresi Hedonis — {environmentVariables[selectedEnv]}
                </h4>

                {estimation.ok ? (
                    <>
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(150px, 1fr))', gap: 12 }}>
                            {[
                                { label: 'Harga Implisit Marginal (δ)', value: formatRupiah(estimation.delta, 8), color: 'var(--primary)' },
                                { label: 'MWTP Lingkungan', value: `Rp${formatRupiah(estimation.mwtp, 0)}`, color: 'var(--warning)' },
                                { label: 'ΔE rata-rata', value: formatRupiah(estimation.mean_delta_e, 4), color: 'var(--info)' },
                                { label: 'Unit Terdampak (M)', value: formatRupiah(estimation.affected_units, 0), color: 'var(--text)' },
                                { label: 'Nilai Agregat', value: `Rp${formatRupiah(estimation.aggregate_value, 0)}`, color: 'var(--success)' },
                            ].map((s) => (
                                <div key={s.label}>
                                    <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{s.label}</div>
                                    <div style={{ fontSize: 15, fontWeight: 800, color: s.color }}>{s.value}</div>
                                </div>
                            ))}
                        </div>
                        <p style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 10, lineHeight: 1.55 }}>
                            n = {estimation.property_count} properti · R² = {formatRupiah(estimation.r_squared, 4)} ·
                            MWTP = δ × harga rata-rata · Nilai agregat = MWTP × ΔE × M.
                            {estimation.dropped_no_variation?.length > 0 && ` Variabel tanpa variasi dilewati: ${estimation.dropped_no_variation.join(', ')}.`}
                        </p>
                    </>
                ) : (
                    <p style={{ fontSize: 12, color: 'var(--danger)', lineHeight: 1.55 }}>
                        {estimation.message} Minimal {minProperties} properti diperlukan agar δ dapat diestimasi.
                    </p>
                )}
            </div>

            <div className="table-wrapper">
                <table className="data-table">
                    <thead>
                        <tr>
                            <th>ID Properti</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th style={{ textAlign: 'right' }}>Harga</th>
                            <th style={{ textAlign: 'right' }}>L. Tanah</th>
                            <th style={{ textAlign: 'right' }}>L. Bangunan</th>
                            <th style={{ textAlign: 'right' }}>AQI</th>
                            <th style={{ textAlign: 'right' }}>Bising</th>
                            <th style={{ textAlign: 'right' }}>ΔE</th>
                            {canManage && <th>Aksi</th>}
                        </tr>
                    </thead>
                    <tbody>
                        {records.data.map((r) => (
                            <tr key={r.id}>
                                <td style={{ fontSize: 13, fontWeight: 600 }}>{r.property_code}</td>
                                <td style={{ fontSize: 13 }}>{r.property_type}</td>
                                <td style={{ fontSize: 13 }}>{r.location}</td>
                                <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 700 }}>Rp{formatRupiah(r.transaction_price, 0)}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.land_area ? `${formatRupiah(r.land_area, 0)} m²` : '-'}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.building_area ? `${formatRupiah(r.building_area, 0)} m²` : '-'}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.air_quality_index ?? '-'}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.noise_level ? `${formatRupiah(r.noise_level, 0)} dB` : '-'}</td>
                                <td style={{ textAlign: 'right', fontSize: 13 }}>{r.delta_env_quality ?? '-'}</td>
                                {canManage && (
                                    <td>
                                        <div style={{ display: 'flex', gap: 4 }}>
                                            <Link href={route('admin.modules.hpm.edit', [project.id, r.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                            <ConfirmDeleteButton href={route('admin.modules.hpm.destroy', [project.id, r.id])} message={`Hapus properti ${r.property_code}?`} />
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
