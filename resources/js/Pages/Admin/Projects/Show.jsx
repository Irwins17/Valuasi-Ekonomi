import { useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import Pagination from '../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../Components/ui/ConfirmDeleteButton';
import { formatTriliun, formatRupiah, toNumber } from '../../../lib/format';

const SERVICE_CATEGORY_LABELS = {
    provisioning: 'Provisioning',
    regulating: 'Regulating',
    supporting: 'Supporting',
    cultural: 'Cultural',
};

function EcosystemValuationSection({ ecosystemIndices }) {
    const [activeIndexNumber, setActiveIndexNumber] = useState(ecosystemIndices[0]?.index_number);
    const [expandedLandCovers, setExpandedLandCovers] = useState({});

    if (!ecosystemIndices?.length) return null;

    const current = ecosystemIndices.find((i) => i.index_number === activeIndexNumber) || ecosystemIndices[0];

    function toggleLandCover(id) {
        setExpandedLandCovers((prev) => ({ ...prev, [id]: !prev[id] }));
    }

    return (
        <div className="card" style={{ marginBottom: 24 }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 8, flexWrap: 'wrap', gap: 8 }}>
                <h3 style={{ fontSize: 15, fontWeight: 700 }}>Jasa Ekosistem — Valuasi Tutupan Lahan</h3>
                <div style={{ display: 'flex', gap: 6 }}>
                    {ecosystemIndices.map((idx) => (
                        <button
                            key={idx.index_number}
                            type="button"
                            className={`btn btn-sm ${idx.index_number === current.index_number ? 'btn-primary' : 'btn-outline'}`}
                            onClick={() => setActiveIndexNumber(idx.index_number)}
                        >
                            Indeks {idx.index_number}
                        </button>
                    ))}
                </div>
            </div>
            {current.notes && <p style={{ fontSize: 12, color: 'var(--text-muted)', marginBottom: 16 }}>{current.notes}</p>}

            <div className="stat-card" style={{ textAlign: 'center', marginBottom: 20, maxWidth: 280 }}>
                <div className="stat-label">TEV — {current.name}</div>
                <div className="stat-value" style={{ fontSize: 20, color: 'var(--primary)' }}>Rp{formatTriliun(current.tev, 2)}T</div>
            </div>

            {current.land_covers.map((lc) => (
                <div key={lc.id} style={{ border: '1px solid var(--border)', borderRadius: 8, padding: 12, marginBottom: 12 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                        <div>
                            <strong>{lc.name}</strong>
                            {lc.area_ha != null
                                ? <span style={{ fontSize: 12, color: 'var(--text-muted)', marginLeft: 8 }}>{formatRupiah(lc.area_ha, 2)} ha</span>
                                : <span className="badge badge-warning" style={{ marginLeft: 8 }}>Data belum lengkap</span>}
                        </div>
                        <div style={{ fontWeight: 700 }}>Rp{formatRupiah(lc.total, 0)}</div>
                    </div>
                    {lc.notes && <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 6 }}>{lc.notes}</p>}
                    {lc.items.length > 0 && (
                        <>
                            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(120px, 1fr))', gap: 8, marginTop: 10, fontSize: 12 }}>
                                {Object.entries(lc.by_category).filter(([, v]) => Number(v) > 0).map(([cat, val]) => (
                                    <div key={cat}>
                                        <span style={{ color: 'var(--text-muted)' }}>{SERVICE_CATEGORY_LABELS[cat]}</span><br />
                                        <strong>Rp{formatRupiah(val, 0)}</strong>
                                    </div>
                                ))}
                            </div>
                            <button type="button" className="btn btn-sm btn-ghost" style={{ marginTop: 10, padding: '4px 0' }} onClick={() => toggleLandCover(lc.id)}>
                                {expandedLandCovers[lc.id] ? 'Sembunyikan rincian' : `Lihat rincian (${lc.items.length} item)`}
                            </button>
                            {expandedLandCovers[lc.id] && (
                                <div className="table-wrapper" style={{ marginTop: 8 }}>
                                    <table className="data-table">
                                        <thead>
                                            <tr>
                                                <th>Item</th>
                                                <th>Jasa</th>
                                                <th style={{ textAlign: 'right' }}>Produktivitas</th>
                                                <th style={{ textAlign: 'right' }}>Harga/unit</th>
                                                <th style={{ textAlign: 'right' }}>Total Nilai Ekonomi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {lc.items.map((item) => (
                                                <tr key={item.id}>
                                                    <td style={{ fontSize: 13 }}>{item.item_name}</td>
                                                    <td style={{ fontSize: 12 }}><span className="badge badge-info">{SERVICE_CATEGORY_LABELS[item.service_category]}</span></td>
                                                    <td style={{ textAlign: 'right', fontSize: 13 }}>{formatRupiah(item.productivity_value, 2)} {item.productivity_unit}</td>
                                                    <td style={{ textAlign: 'right', fontSize: 13 }}>Rp{formatRupiah(item.unit_price, 0)}</td>
                                                    <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 600 }}>Rp{formatRupiah(item.total_value, 0)}</td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            )}
                        </>
                    )}
                </div>
            ))}

            {current.cultural_items.length > 0 && (
                <div style={{ border: '1px solid var(--border)', borderRadius: 8, padding: 12 }}>
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                        <strong>Cultural Services (tingkat kawasan)</strong>
                        <div style={{ fontWeight: 700 }}>Rp{formatRupiah(current.cultural_total, 0)}</div>
                    </div>
                    <div className="table-wrapper" style={{ marginTop: 8 }}>
                        <table className="data-table">
                            <tbody>
                                {current.cultural_items.map((item) => (
                                    <tr key={item.id}>
                                        <td style={{ fontSize: 13 }}>{item.item_name}</td>
                                        <td style={{ textAlign: 'right', fontSize: 13, fontWeight: 600 }}>Rp{formatRupiah(item.total_value, 0)}</td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            )}
        </div>
    );
}

export default function Show({ project, benefits, costs, ecosystemIndices = [] }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin;
    const bcr = toNumber(project.bcr);

    function calculateTev(e) {
        e.preventDefault();
        router.post(route('admin.projects.calculateTEV', project.id));
    }

    return (
        <AdminLayout title={project.name} subtitle={`Detail Proyek ${project.code}`}>
            <Head title={project.name} />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24 }}>
                <Link href={route('admin.projects.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13 }}>← Kembali ke Daftar Proyek</Link>
                {canManage && (
                    <div style={{ display: 'flex', gap: 8 }}>
                        <form onSubmit={calculateTev} style={{ display: 'inline' }}>
                            <button type="submit" className="btn btn-sm btn-secondary">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="4" y="2" width="16" height="20" rx="2" /><path d="M8 6h8M8 10h.01M12 10h.01M16 10h.01M8 14h.01M12 14h.01M16 14h.01M8 18h.01M12 18h.01" /></svg>
                                Hitung TEV
                            </button>
                        </form>
                        <Link href={route('admin.projects.edit', project.id)} className="btn btn-sm btn-outline">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 20h9" /><path d="M16.5 3.5a2.12 2.12 0 013 3L7 19l-4 1 1-4z" /></svg>
                            Edit
                        </Link>
                    </div>
                )}
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 16, marginBottom: 28 }}>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">TEV</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--primary)' }}>Rp{formatTriliun(project.tev, 2)}T</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Total Manfaat</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--success)' }}>Rp{formatTriliun(project.total_benefits, 2)}T</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">Total Biaya</div>
                    <div className="stat-value" style={{ fontSize: 22, color: 'var(--danger)' }}>Rp{formatTriliun(project.total_costs, 2)}T</div>
                </div>
                <div className="stat-card" style={{ textAlign: 'center' }}>
                    <div className="stat-label">BCR</div>
                    <div className="stat-value" style={{ fontSize: 22, color: bcr >= 1 ? 'var(--success)' : 'var(--danger)' }}>{formatRupiah(bcr, 4)}</div>
                </div>
            </div>

            <div className="card" style={{ marginBottom: 24 }}>
                <h3 style={{ fontSize: 15, fontWeight: 700, marginBottom: 16 }}>Modul Data Entry</h3>
                <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 12 }}>
                    <Link href={route('admin.modules.eop.index', project.id)} className="card module-tile">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 22v-9" /><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6" /><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2" /><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2" /></svg>
                        <div style={{ fontWeight: 700, color: 'var(--text)', marginTop: 8 }}>EOP</div>
                        <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{project.eop_data_count} records</div>
                    </Link>
                    <Link href={route('admin.modules.tcm.index', project.id)} className="card module-tile">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 17h14M5 17a2 2 0 01-2-2v-2.5L5 8h14l2 4.5V15a2 2 0 01-2 2M5 17v2a1 1 0 001 1h1a1 1 0 001-1v-2m8 0v2a1 1 0 001 1h1a1 1 0 001-1v-2" /><circle cx="7.5" cy="14.5" r="1" /><circle cx="16.5" cy="14.5" r="1" /></svg>
                        <div style={{ fontWeight: 700, color: 'var(--text)', marginTop: 8 }}>TCM</div>
                        <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{project.tcm_data_count} records</div>
                    </Link>
                    <Link href={route('admin.modules.cvm.index', project.id)} className="card module-tile">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /><path d="M10 9H8" /></svg>
                        <div style={{ fontWeight: 700, color: 'var(--text)', marginTop: 8 }}>CVM</div>
                        <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>{project.cvm_data_count} records</div>
                    </Link>
                </div>
            </div>

            <EcosystemValuationSection ecosystemIndices={ecosystemIndices} />

            <div className="card" style={{ marginBottom: 24 }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                    <h3 style={{ fontSize: 15, fontWeight: 700 }}>Manfaat (Benefits)</h3>
                    {canManage && <Link href={route('admin.benefits.create', project.id)} className="btn btn-sm btn-primary">+ Tambah</Link>}
                </div>
                {benefits.data.length ? (
                    <>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead><tr><th>Kategori</th><th>Sub</th><th>Deskripsi</th><th>Metode</th><th style={{ textAlign: 'right' }}>Nilai</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {benefits.data.map((b) => (
                                        <tr key={b.id}>
                                            <td><span className={`badge ${b.category === 'direct_use' ? 'badge-primary' : b.category === 'indirect_use' ? 'badge-success' : 'badge-purple'}`}>{b.category.replace('_', ' ')}</span></td>
                                            <td style={{ fontSize: 13 }}>{b.subcategory?.replace('_', ' ')}</td>
                                            <td style={{ fontSize: 13 }}>{(b.description || '').length > 40 ? b.description.slice(0, 40) + '…' : b.description}</td>
                                            <td><span className="badge badge-info">{b.method_used}</span></td>
                                            <td style={{ textAlign: 'right', fontWeight: 700 }}>Rp{formatRupiah(b.value)}</td>
                                            <td>
                                                {canManage && (
                                                    <div style={{ display: 'flex', gap: 4 }}>
                                                        <Link href={route('admin.benefits.edit', [project.id, b.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                        <ConfirmDeleteButton href={route('admin.benefits.destroy', [project.id, b.id])} />
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination paginator={benefits} />
                    </>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 20 }}>Belum ada data manfaat.</p>
                )}
            </div>

            <div className="card">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 16 }}>
                    <h3 style={{ fontSize: 15, fontWeight: 700 }}>Biaya (Costs)</h3>
                    {canManage && <Link href={route('admin.costs.create', project.id)} className="btn btn-sm btn-primary">+ Tambah</Link>}
                </div>
                {costs.data.length ? (
                    <>
                        <div className="table-wrapper">
                            <table className="data-table">
                                <thead><tr><th>Kategori</th><th>Sub</th><th>Deskripsi</th><th>Tipe</th><th style={{ textAlign: 'right' }}>Nilai</th><th>Aksi</th></tr></thead>
                                <tbody>
                                    {costs.data.map((c) => (
                                        <tr key={c.id}>
                                            <td><span className={`badge ${c.category === 'direct_cost' ? 'badge-warning' : 'badge-danger'}`}>{c.category.replace('_', ' ')}</span></td>
                                            <td style={{ fontSize: 13 }}>{c.subcategory?.replace('_', ' ')}</td>
                                            <td style={{ fontSize: 13 }}>{(c.description || '').length > 40 ? c.description.slice(0, 40) + '…' : c.description}</td>
                                            <td style={{ fontSize: 13 }}>{c.payment_type || '-'}</td>
                                            <td style={{ textAlign: 'right', fontWeight: 700 }}>Rp{formatRupiah(c.value)}</td>
                                            <td>
                                                {canManage && (
                                                    <div style={{ display: 'flex', gap: 4 }}>
                                                        <Link href={route('admin.costs.edit', [project.id, c.id])} className="btn btn-sm btn-ghost">Edit</Link>
                                                        <ConfirmDeleteButton href={route('admin.costs.destroy', [project.id, c.id])} />
                                                    </div>
                                                )}
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                        <Pagination paginator={costs} />
                    </>
                ) : (
                    <p style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 20 }}>Belum ada data biaya.</p>
                )}
            </div>

            <style>{`
                .module-tile { text-decoration: none; text-align: center; padding: 20px; border: 1px solid var(--border); color: var(--text-secondary); }
                .module-tile:hover { border-color: var(--primary); color: var(--primary); box-shadow: none; }
            `}</style>
        </AdminLayout>
    );
}
