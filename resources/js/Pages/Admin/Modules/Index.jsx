import { useState } from 'react';
import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

const CATEGORY_COLORS = {
    provisioning: '#10b981',
    regulating: '#3b82f6',
    supporting: '#8b5cf6',
    cultural: '#f59e0b',
};

const MODULE_ICONS = {
    DUV: <><path d="M12 1v22" /><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6" /></>,
    EOP: <><path d="M12 22v-9" /><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6" /><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2" /><path d="M15 13a5 5 0 016 5c-1 .6-2.4 1-4 1a7 7 0 01-5-2" /></>,
    TCM: <><path d="M5 17h14M5 17a2 2 0 01-2-2v-2.5L5 8h14l2 4.5V15a2 2 0 01-2 2M5 17v2a1 1 0 001 1h1a1 1 0 001-1v-2m8 0v2a1 1 0 001 1h1a1 1 0 001-1v-2" /><circle cx="7.5" cy="14.5" r="1" /><circle cx="16.5" cy="14.5" r="1" /></>,
    CVM: <><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /><path d="M10 9H8" /></>,
    HPM: <><path d="M3 3v18h18" /><rect x="7" y="12" width="3" height="6" /><rect x="12" y="8" width="3" height="10" /><rect x="17" y="5" width="3" height="13" /></>,
    ABM: <><circle cx="12" cy="5" r="2" /><circle cx="5" cy="18" r="2" /><circle cx="19" cy="18" r="2" /><path d="M12 7v4M12 11l-5.5 5M12 11l5.5 5" /></>,
    CE: <><path d="M9 2v6L4 20a2 2 0 002 2h12a2 2 0 002-2L15 8V2" /><path d="M9 2h6" /><path d="M7 16h10" /></>,
    FOOD: <><path d="M12 22v-9" /><path d="M15.5 9A5.5 5.5 0 0012 3a5.5 5.5 0 00-3.5 6" /><path d="M9 13a5 5 0 00-6 5c1 .6 2.4 1 4 1a7 7 0 005-2" /></>,
    RAWMAT: <><path d="M21 16V8a2 2 0 00-1-1.73l-7-4a2 2 0 00-2 0l-7 4A2 2 0 003 8v8a2 2 0 001 1.73l7 4a2 2 0 002 0l7-4A2 2 0 0021 16z" /><path d="M3.27 6.96L12 12.01l8.73-5.05" /><path d="M12 22.08V12" /></>,
    GENRES: <><path d="M4 3v18" /><path d="M20 3v18" /><path d="M4 8c6 0 10-4 16-4" /><path d="M4 16c6 0 10 4 16 4" /><path d="M4 12h16" /></>,
    CLIMATE: <><path d="M17.5 19a4.5 4.5 0 00.5-8.98A6 6 0 006 9.5a4.5 4.5 0 00.5 9.5z" /><path d="M8 19v2M12 19v3M16 19v2" /></>,
    EROSION: <><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" /></>,
    WATER: <><path d="M12 2.69l5.66 5.66a8 8 0 11-11.31 0z" /></>,
};

const DEFAULT_ICON = <><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></>;

function ModuleIcon({ code, color, size = 20 }) {
    return (
        <span style={{ width: size * 1.9, height: size * 1.9, borderRadius: 10, background: `${color}1A`, color, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
            <svg width={size} height={size} viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                {MODULE_ICONS[code] || DEFAULT_ICON}
            </svg>
        </span>
    );
}

function MetaRow({ icon, label, value, valueColor, valueIcon }) {
    return (
        <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', gap: 8, padding: '6px 0', borderBottom: '1px solid var(--border-light)' }}>
            <span style={{ display: 'flex', alignItems: 'center', gap: 6, fontSize: 12, color: 'var(--text-muted)' }}>
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">{icon}</svg>
                {label}
            </span>
            <span style={{ display: 'flex', alignItems: 'center', gap: 5, fontSize: 12, fontWeight: 600, color: valueColor || 'var(--text)', textAlign: 'right' }}>
                {valueIcon && <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">{valueIcon}</svg>}
                {value}
            </span>
        </div>
    );
}

function ModuleCard({ module, project, canManage, categoryLabels }) {
    const color = CATEGORY_COLORS[module.service_category] || '#64748b';
    const isActive = module.status === 'aktif';

    return (
        <div className="card" style={{ display: 'flex', flexDirection: 'column' }}>
            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'flex-start', gap: 10, marginBottom: 10 }}>
                <div style={{ display: 'flex', gap: 10, minWidth: 0 }}>
                    <ModuleIcon code={module.code} color={color} />
                    <div style={{ minWidth: 0 }}>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 6, flexWrap: 'wrap' }}>
                            <span style={{ fontWeight: 700, fontSize: 14 }}>{module.name}</span>
                            <code style={{ fontSize: 10, fontWeight: 600, color: 'var(--text-muted)', background: 'var(--surface-alt)', border: '1px solid var(--border)', borderRadius: 4, padding: '1px 5px' }}>{module.code}</code>
                        </div>
                        <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 4, lineHeight: 1.5 }}>{module.description}</p>
                    </div>
                </div>
                <span className={`badge ${isActive ? 'badge-success' : 'badge-warning'}`} style={{ flexShrink: 0 }}>
                    {isActive ? 'Aktif' : 'Draft'}
                </span>
            </div>

            <div style={{ marginTop: 'auto' }}>
                <MetaRow
                    icon={<><circle cx="12" cy="12" r="10" /><path d="M12 6v6l4 2" /></>}
                    label="Kelompok Metode"
                    value={module.method_group_label}
                    valueColor="var(--primary)"
                />
                <MetaRow
                    icon={<><circle cx="12" cy="12" r="10" /><path d="M2 12h20" /><path d="M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z" /></>}
                    label="Cakupan Jasa Ekosistem"
                    value={categoryLabels[module.service_category] || '-'}
                    valueColor={color}
                />
                <MetaRow
                    icon={<><rect x="3" y="4" width="18" height="18" rx="2" /><path d="M16 2v4M8 2v4M3 10h18" /></>}
                    label="Jumlah Data"
                    value={`${module.record_count} record`}
                />

                <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 8, marginTop: 14 }}>
                    {module.route_url ? (
                        <Link href={module.route_url} className="btn btn-sm btn-outline">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" /><path d="M15 3h6v6" /><path d="M10 14L21 3" /></svg>
                            Buka
                        </Link>
                    ) : (
                        <button type="button" className="btn btn-sm btn-outline" disabled title="Halaman data modul ini belum tersedia" style={{ opacity: 0.45, cursor: 'not-allowed' }}>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" /><path d="M15 3h6v6" /><path d="M10 14L21 3" /></svg>
                            Buka
                        </button>
                    )}
                    {canManage ? (
                        <Link href={route('admin.modules.configure', [project.id, module.code])} className="btn btn-sm btn-ghost" style={{ border: '1px solid var(--border)' }}>
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="3" /><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 01-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06A1.65 1.65 0 004.6 15a1.65 1.65 0 00-1.51-1H3a2 2 0 010-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06A1.65 1.65 0 009 4.6a1.65 1.65 0 001-1.51V3a2 2 0 014 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06A1.65 1.65 0 0019.4 9a1.65 1.65 0 001.51 1H21a2 2 0 010 4h-.09a1.65 1.65 0 00-1.51 1z" /></svg>
                            Konfigurasi
                        </Link>
                    ) : <span />}
                </div>
            </div>
        </div>
    );
}

function SummaryCard({ label, value, caption, color, icon }) {
    return (
        <div className="card" style={{ display: 'flex', alignItems: 'center', gap: 14 }}>
            <span style={{ width: 46, height: 46, borderRadius: '50%', background: `${color}1A`, color, display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">{icon}</svg>
            </span>
            <div>
                <div style={{ fontSize: 13, color: 'var(--text-secondary)', fontWeight: 500 }}>{label}</div>
                <div style={{ fontSize: 24, fontWeight: 800, lineHeight: 1.2 }}>{value}</div>
                <div style={{ fontSize: 11, color: 'var(--text-muted)' }}>{caption}</div>
            </div>
        </div>
    );
}

export default function Index({ project, modules, summary, serviceCategories }) {
    const { auth } = usePage().props;
    const canManage = auth.user.isAdmin;
    const [filter, setFilter] = useState('all');

    const visible = filter === 'all' ? modules : modules.filter((m) => m.service_category === filter);
    const noteModules = modules.filter((m) => m.notes && !m.is_advanced && m.status === 'aktif').slice(0, 4);

    return (
        <AdminLayout title={`Modul Valuasi — ${project.name}`} subtitle={`Proyek ${project.code}`}>
            <Head title={`Modul Valuasi — ${project.name}`} />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 24, gap: 12, flexWrap: 'wrap' }}>
                <Link href={route('admin.projects.show', project.id)} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13 }}>← Kembali ke Proyek</Link>
                {canManage && (
                    <Link href={route('admin.modules.create', project.id)} className="btn btn-sm btn-primary">+ Tambah Modul</Link>
                )}
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'repeat(3,1fr)', gap: 16, marginBottom: 24 }}>
                <SummaryCard
                    label="Modul Aktif" value={summary.active} caption="Sedang digunakan dalam proyek" color="#10b981"
                    icon={<><path d="M22 11.08V12a10 10 0 11-5.93-9.14" /><path d="M22 4L12 14.01l-3-3" /></>}
                />
                <SummaryCard
                    label="Modul Draft" value={summary.draft} caption="Belum diaktifkan" color="#f59e0b"
                    icon={<><circle cx="12" cy="12" r="10" /><path d="M12 8v4M12 16h.01" /></>}
                />
                <SummaryCard
                    label="Total Record" value={summary.records} caption="Total data pada semua modul" color="#6366f1"
                    icon={<><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8M16 17H8M10 9H8" /></>}
                />
            </div>

            <div style={{ display: 'grid', gridTemplateColumns: 'minmax(0,1fr) 300px', gap: 16, alignItems: 'start' }}>
                <div>
                    <div className="card" style={{ display: 'flex', alignItems: 'center', gap: 10, flexWrap: 'wrap', marginBottom: 16, padding: '12px 16px' }}>
                        <span style={{ fontSize: 12, fontWeight: 600, color: 'var(--text-secondary)' }}>Filter Cakupan Jasa Ekosistem:</span>
                        <button type="button" onClick={() => setFilter('all')} className={`btn btn-sm ${filter === 'all' ? 'btn-primary' : 'btn-ghost'}`} style={filter === 'all' ? undefined : { border: '1px solid var(--border)' }}>
                            Semua
                        </button>
                        {Object.entries(serviceCategories).map(([key, label]) => (
                            <button
                                key={key}
                                type="button"
                                onClick={() => setFilter(key)}
                                className={`btn btn-sm ${filter === key ? 'btn-primary' : 'btn-ghost'}`}
                                style={filter === key ? undefined : { border: '1px solid var(--border)' }}
                            >
                                {label}
                            </button>
                        ))}
                    </div>

                    {visible.length ? (
                        <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fill, minmax(280px, 1fr))', gap: 16 }}>
                            {visible.map((module) => (
                                <ModuleCard key={module.code} module={module} project={project} canManage={canManage} categoryLabels={serviceCategories} />
                            ))}
                        </div>
                    ) : (
                        <div className="card" style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>
                            Tidak ada modul pada kategori ini.
                        </div>
                    )}
                </div>

                <div className="card">
                    <div style={{ display: 'flex', alignItems: 'center', gap: 8, marginBottom: 14 }}>
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" strokeWidth="2"><circle cx="12" cy="12" r="10" /><path d="M12 16v-4" /><path d="M12 8h.01" /></svg>
                        <h3 style={{ fontSize: 14, fontWeight: 700 }}>Catatan Modul</h3>
                    </div>

                    {noteModules.map((m) => (
                        <div key={m.code} style={{ display: 'flex', gap: 10, marginBottom: 14 }}>
                            <ModuleIcon code={m.code} color={CATEGORY_COLORS[m.service_category] || '#64748b'} size={15} />
                            <div>
                                <div style={{ fontWeight: 700, fontSize: 12.5 }}>{m.name}</div>
                                <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.55, marginTop: 2 }}>{m.notes}</p>
                            </div>
                        </div>
                    ))}

                    <div style={{ borderTop: '1px solid var(--border)', paddingTop: 12, marginTop: 4 }}>
                        <div style={{ fontWeight: 700, fontSize: 12.5, marginBottom: 4 }}>Modul Lanjutan (Opsional)</div>
                        <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.55 }}>
                            HPM, ABM, dan CE merupakan metode lanjutan yang dapat digunakan sesuai kebutuhan analisis dan ketersediaan data.
                        </p>
                    </div>

                    <div style={{ borderTop: '1px solid var(--border)', paddingTop: 12, marginTop: 12 }}>
                        <div style={{ fontWeight: 700, fontSize: 12.5, marginBottom: 8 }}>Status Modul</div>
                        <div style={{ display: 'flex', gap: 7, marginBottom: 8 }}>
                            <span style={{ width: 8, height: 8, borderRadius: '50%', background: '#10b981', marginTop: 4, flexShrink: 0 }} />
                            <div>
                                <div style={{ fontSize: 12, fontWeight: 600 }}>Aktif</div>
                                <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.5 }}>Modul siap digunakan dan berkontribusi pada perhitungan valuasi.</p>
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 7 }}>
                            <span style={{ width: 8, height: 8, borderRadius: '50%', background: '#f59e0b', marginTop: 4, flexShrink: 0 }} />
                            <div>
                                <div style={{ fontSize: 12, fontWeight: 600 }}>Draft</div>
                                <p style={{ fontSize: 11.5, color: 'var(--text-muted)', lineHeight: 1.5 }}>Modul disimpan sebagai draf dan belum digunakan dalam analisis.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
