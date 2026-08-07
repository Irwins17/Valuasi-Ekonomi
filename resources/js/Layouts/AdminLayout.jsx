import { useEffect, useState } from 'react';
import { Link, usePage, router } from '@inertiajs/react';
import ErrorBoundary from '../Components/ui/ErrorBoundary';

function NavLink({ href, active, children }) {
    return (
        <Link href={href} className={`sidebar-link ${active ? 'active' : ''}`}>
            {children}
        </Link>
    );
}

function FlashAlert({ message, type }) {
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        const t = setTimeout(() => setVisible(false), 4000);
        return () => clearTimeout(t);
    }, [message]);

    if (!visible) return null;

    return (
        <div className={`alert alert-${type}`} style={{ marginBottom: 20 }}>
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
                {type === 'success' ? <path d="M22 11.08V12a10 10 0 11-5.93-9.14" /> : <circle cx="12" cy="12" r="10" />}
                {type === 'success' && <path d="M22 4L12 14.01l-3-3" />}
            </svg>
            <span>{message}</span>
        </div>
    );
}

export default function AdminLayout({ title = 'Dashboard', subtitle, children }) {
    const { props, url: currentPath } = usePage();
    const { auth, flash, errors } = props;
    const user = auth.user;

    function routeIs(prefix) {
        return currentPath === prefix || currentPath.startsWith(prefix);
    }

    function logout(e) {
        e.preventDefault();
        router.post(route('logout'));
    }

    return (
        <div className="admin-app page-enter" style={{ display: 'flex', minHeight: '100vh' }}>
            <aside style={{ width: 252, background: '#0f172a', color: '#fff', position: 'fixed', top: 0, bottom: 0, left: 0, overflowY: 'auto', zIndex: 50, display: 'flex', flexDirection: 'column' }}>
                <div style={{ padding: '18px 20px', borderBottom: '1px solid #1e293b' }}>
                    <Link href={route('dashboard')} style={{ display: 'flex', alignItems: 'center', gap: 10, textDecoration: 'none' }}>
                        <div>
                            <div style={{ fontWeight: 600, fontSize: 14, color: '#fff', letterSpacing: '-0.01em' }}>Valuasi Ekonomi</div>
                            <div style={{ fontSize: 11, color: '#64748b' }}>Panel Admin</div>
                        </div>
                    </Link>
                </div>

                <nav style={{ padding: 12, flex: 1 }}>
                    <div style={{ margin: '0 0 8px', padding: '0 12px' }}>
                        <span style={{ fontSize: 11, fontWeight: 600, color: '#475569', textTransform: 'uppercase', letterSpacing: '0.08em' }}>Menu Utama</span>
                    </div>

                    <NavLink href={route('dashboard')} active={currentPath === '/admin/dashboard'}>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><rect x="3" y="3" width="7" height="7" rx="1" /><rect x="14" y="3" width="7" height="7" rx="1" /><rect x="3" y="14" width="7" height="7" rx="1" /><rect x="14" y="14" width="7" height="7" rx="1" /></svg>
                        Dashboard
                    </NavLink>

                    <NavLink href={route('admin.projects.index')} active={routeIs('/admin/projects')}>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z" /></svg>
                        Proyek Valuasi
                    </NavLink>

                    {(user?.isAdmin || user?.isAnalyst) && (
                        <>
                            <div style={{ margin: '16px 0 8px', padding: '0 12px' }}>
                                <span style={{ fontSize: 11, fontWeight: 600, color: '#475569', textTransform: 'uppercase', letterSpacing: '0.08em' }}>Analitik</span>
                            </div>
                            <NavLink href={route('admin.sensitivity.index')} active={routeIs('/admin/sensitivity')}>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 20V10" /><path d="M18 20V4" /><path d="M6 20v-4" /></svg>
                                Analisis Sensitivitas
                            </NavLink>
                        </>
                    )}

                    {user?.isAdmin && (
                        <>
                            <div style={{ margin: '16px 0 8px', padding: '0 12px' }}>
                                <span style={{ fontSize: 11, fontWeight: 600, color: '#475569', textTransform: 'uppercase', letterSpacing: '0.08em' }}>Administrasi</span>
                            </div>
                            <NavLink href={route('admin.users.index')} active={routeIs('/admin/users')}>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4-4v2" /><circle cx="9" cy="7" r="4" /><path d="M23 21v-2a4 4 0 00-3-3.87" /><path d="M16 3.13a4 4 0 010 7.75" /></svg>
                                Pengguna
                            </NavLink>
                            <NavLink href={route('admin.master.prices.index')} active={routeIs('/admin/master-data')}>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M4 7V4h16v3" /><path d="M9 20h6" /><path d="M12 4v16" /></svg>
                                Master Data
                            </NavLink>
                            <NavLink href={route('admin.audit.index')} active={routeIs('/admin/audit-logs')}>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z" /><path d="M14 2v6h6" /><path d="M16 13H8" /><path d="M16 17H8" /><path d="M10 9H8" /></svg>
                                Audit Log
                            </NavLink>
                        </>
                    )}
                </nav>

                <div style={{ padding: 16, borderTop: '1px solid #1e293b' }}>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 10, marginBottom: 12 }}>
                        <div style={{ width: 34, height: 34, borderRadius: '50%', background: '#1e293b', border: '1px solid #334155', display: 'flex', alignItems: 'center', justifyContent: 'center', flexShrink: 0 }}>
                            <span style={{ color: '#cbd5e1', fontWeight: 600, fontSize: 12 }}>{(user?.name || '').slice(0, 2).toUpperCase()}</span>
                        </div>
                        <div style={{ flex: 1, minWidth: 0 }}>
                            <div style={{ fontSize: 13, fontWeight: 600, color: '#e2e8f0', overflow: 'hidden', textOverflow: 'ellipsis', whiteSpace: 'nowrap' }}>{user?.name}</div>
                            <div style={{ fontSize: 11, color: '#64748b' }}>{user?.role || '-'}</div>
                        </div>
                    </div>
                    <form onSubmit={logout}>
                        <button type="submit" style={{ width: '100%', padding: 8, background: '#1e293b', border: '1px solid #334155', borderRadius: 8, color: '#94a3b8', fontSize: 13, fontWeight: 500, cursor: 'pointer', fontFamily: 'inherit' }}>
                            Keluar
                        </button>
                    </form>
                </div>
            </aside>

            <div style={{ flex: 1, marginLeft: 252 }}>
                <header style={{ background: 'var(--surface)', borderBottom: '1px solid var(--border)', padding: '16px 32px', display: 'flex', justifyContent: 'space-between', alignItems: 'center', position: 'sticky', top: 0, zIndex: 40 }}>
                    <div>
                        <h1 style={{ fontSize: 20, fontWeight: 700, color: 'var(--text)' }}>{title}</h1>
                        {subtitle && <p style={{ fontSize: 13, color: 'var(--text-muted)', marginTop: 2 }}>{subtitle}</p>}
                    </div>
                    <div style={{ display: 'flex', alignItems: 'center', gap: 16 }}>
                        <span style={{ fontSize: 13, color: 'var(--text-muted)' }}>
                            {new Date().toLocaleDateString('id-ID', { weekday: 'long', day: '2-digit', month: 'long', year: 'numeric' })}
                        </span>
                        <a href={route('landing')} className="btn btn-sm btn-ghost" target="_blank" rel="noreferrer">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6" /><path d="M15 3h6v6" /><path d="M10 14L21 3" /></svg>
                            Situs Publik
                        </a>
                    </div>
                </header>

                <div style={{ padding: '28px 32px' }}>
                    {errors && Object.keys(errors).length > 0 && (
                        <div className="alert alert-danger" style={{ marginBottom: 20 }}>
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" /><path d="M15 9l-6 6" /><path d="M9 9l6 6" /></svg>
                            <div>
                                {Object.values(errors).map((error, i) => (
                                    <div key={i}>{error}</div>
                                ))}
                            </div>
                        </div>
                    )}

                    {flash?.success && <FlashAlert message={flash.success} type="success" />}
                    {flash?.error && <FlashAlert message={flash.error} type="danger" />}

                    <ErrorBoundary>{children}</ErrorBoundary>
                </div>
            </div>
        </div>
    );
}
