import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import BarChart from '../../Components/charts/BarChart';
import { toNumber, formatTriliun } from '../../lib/format';

const STATUS_BADGE = {
    published: 'badge-success',
    in_progress: 'badge-warning',
    completed: 'badge-info',
    draft: 'badge-gray',
};

export default function Dashboard({
    totalProjects,
    publishedProjects,
    inProgressProjects,
    totalEOPRecords,
    totalTCMRecords,
    totalCVMRecords,
    totalUsers,
    totalSurveyors,
    aggregatedTEV,
    lastProjects,
    monthlyStats,
}) {
    const monthlyLabels = Object.keys(monthlyStats);
    const monthlyData = Object.values(monthlyStats).map((v) => toNumber(v));

    return (
        <AdminLayout title="Dashboard" subtitle="Ringkasan data dan aktivitas terkini">
            <Head title="Dashboard" />

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(4,1fr)', gap: 20, marginBottom: 24 }}>
                <div className="stat-card">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div>
                            <div className="stat-label">Total Proyek</div>
                            <div className="stat-value">{totalProjects}</div>
                        </div>
                        <div className="stat-icon" style={{ background: 'var(--surface-alt)', color: 'var(--text-secondary)' }}>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" /></svg>
                        </div>
                    </div>
                </div>
                <div className="stat-card">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div>
                            <div className="stat-label">Dipublikasikan</div>
                            <div className="stat-value" style={{ color: 'var(--success)' }}>{publishedProjects}</div>
                        </div>
                        <div className="stat-icon" style={{ background: 'var(--surface-alt)', color: 'var(--success)' }}>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 11.08V12a10 10 0 11-5.93-9.14" /><path d="M22 4L12 14.01l-3-3" /></svg>
                        </div>
                    </div>
                </div>
                <div className="stat-card">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div>
                            <div className="stat-label">Dalam Proses</div>
                            <div className="stat-value" style={{ color: 'var(--warning)' }}>{inProgressProjects}</div>
                        </div>
                        <div className="stat-icon" style={{ background: 'var(--surface-alt)', color: 'var(--warning)' }}>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></svg>
                        </div>
                    </div>
                </div>
                <div className="stat-card">
                    <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start' }}>
                        <div>
                            <div className="stat-label">Total TEV</div>
                            <div className="stat-value" style={{ color: 'var(--primary)' }}>Rp{formatTriliun(aggregatedTEV)}T</div>
                        </div>
                        <div className="stat-icon" style={{ background: 'var(--surface-alt)', color: 'var(--primary)' }}>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 1v22" /><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" /></svg>
                        </div>
                    </div>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 20, marginBottom: 24 }}>
                <div className="card" style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                    <div style={{ width: 44, height: 44, borderRadius: 10, background: 'var(--surface-alt)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-secondary)', flexShrink: 0 }}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 22v-9" /><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6" /><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2" /><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2" /></svg>
                    </div>
                    <div><div style={{ fontSize: 22, fontWeight: 700 }}>{totalEOPRecords}</div><div style={{ fontSize: 13, color: 'var(--text-muted)' }}>Data EOP</div></div>
                </div>
                <div className="card" style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                    <div style={{ width: 44, height: 44, borderRadius: 10, background: 'var(--surface-alt)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-secondary)', flexShrink: 0 }}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M5 17h14M5 17a2 2 0 01-2-2v-2.5L5 8h14l2 4.5V15a2 2 0 01-2 2M5 17v2a1 1 0 001 1h1a1 1 0 001-1v-2m8 0v2a1 1 0 001 1h1a1 1 0 001-1v-2" /><circle cx="7.5" cy="14.5" r="1" /><circle cx="16.5" cy="14.5" r="1" /></svg>
                    </div>
                    <div><div style={{ fontSize: 22, fontWeight: 700 }}>{totalTCMRecords}</div><div style={{ fontSize: 13, color: 'var(--text-muted)' }}>Data TCM</div></div>
                </div>
                <div className="card" style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                    <div style={{ width: 44, height: 44, borderRadius: 10, background: 'var(--surface-alt)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'var(--text-secondary)', flexShrink: 0 }}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /></svg>
                    </div>
                    <div><div style={{ fontSize: 22, fontWeight: 700 }}>{totalCVMRecords}</div><div style={{ fontSize: 13, color: 'var(--text-muted)' }}>Data CVM</div></div>
                </div>
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: '2fr 1fr', gap: 20, marginBottom: 28 }}>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Tren Proyek Bulanan</h3>
                    <div style={{ height: 240 }}>
                        <BarChart labels={monthlyLabels} data={monthlyData} colors={Array(monthlyLabels.length).fill('#6366f1')} />
                    </div>
                </div>
                <div className="card">
                    <h3 style={{ fontSize: 16, fontWeight: 700, marginBottom: 20 }}>Ringkasan</h3>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
                        <div style={{ padding: 14, background: 'var(--primary-50)', borderRadius: 'var(--radius-sm)' }}>
                            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>TEV Agregat</div>
                            <div style={{ fontSize: 20, fontWeight: 800, color: 'var(--primary)' }}>Rp{formatTriliun(aggregatedTEV)}T</div>
                        </div>
                        <div style={{ padding: 14, background: '#d1fae5', borderRadius: 'var(--radius-sm)' }}>
                            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Pengguna Aktif</div>
                            <div style={{ fontSize: 20, fontWeight: 800, color: 'var(--success)' }}>{totalUsers}</div>
                        </div>
                        <div style={{ padding: 14, background: '#fef3c7', borderRadius: 'var(--radius-sm)' }}>
                            <div style={{ fontSize: 12, color: 'var(--text-muted)' }}>Surveyor</div>
                            <div style={{ fontSize: 20, fontWeight: 800, color: '#d97706' }}>{totalSurveyors}</div>
                        </div>
                    </div>
                </div>
            </div>

            <div className="card">
                <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }}>
                    <h3 style={{ fontSize: 16, fontWeight: 700 }}>Proyek Terbaru</h3>
                    <Link href={route('admin.projects.index')} className="btn btn-sm btn-ghost">Lihat Semua →</Link>
                </div>
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Kode</th><th>Nama</th><th>Lokasi</th><th>Status</th><th>TEV</th><th style={{ textAlign: 'center' }}>Aksi</th></tr></thead>
                        <tbody>
                            {lastProjects.map((project) => (
                                <tr key={project.id}>
                                    <td><span style={{ fontFamily: 'monospace', fontSize: 13, color: 'var(--text-muted)' }}>{project.code}</span></td>
                                    <td style={{ fontWeight: 600 }}>{project.name}</td>
                                    <td style={{ fontSize: 13, color: 'var(--text-secondary)' }}>{project.location}</td>
                                    <td>
                                        <span className={`badge ${STATUS_BADGE[project.status] || 'badge-gray'}`}>
                                            {project.status.replace('_', ' ').replace(/^./, (c) => c.toUpperCase())}
                                        </span>
                                    </td>
                                    <td style={{ fontWeight: 700, color: 'var(--primary)' }}>{project.tev ? `Rp${formatTriliun(project.tev)}T` : '-'}</td>
                                    <td style={{ textAlign: 'center' }}><Link href={route('admin.projects.show', project.id)} className="btn btn-sm btn-ghost">Lihat</Link></td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
