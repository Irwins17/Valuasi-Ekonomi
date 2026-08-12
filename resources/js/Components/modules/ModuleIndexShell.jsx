import { Head, Link } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import { formatRupiah } from '../../lib/format';

export default function ModuleIndexShell({
    title,
    projectName,
    backHref,
    backLabel,
    createHref,
    createLabel,
    canManage,
    stats = [],
    cardTitle,
    cardSubtitle,
    isEmpty,
    emptyText,
    headerExtra,
    children,
}) {
    return (
        <AdminLayout title={title} subtitle={<>Detail Proyek: <strong style={{ color: 'var(--primary)' }}>{projectName}</strong></>}>
            <Head title={title} />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20, gap: 12, flexWrap: 'wrap' }}>
                <Link href={backHref} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13 }}>← {backLabel}</Link>
                <div style={{ display: 'flex', gap: 8, alignItems: 'center', flexWrap: 'wrap' }}>
                    {headerExtra}
                    {canManage && createHref && (
                        <Link href={createHref} className="btn btn-sm btn-primary">+ {createLabel}</Link>
                    )}
                </div>
            </div>

            {stats.length > 0 && (
                <div style={{ display: 'grid', gridTemplateColumns: `repeat(${stats.length}, 1fr)`, gap: 16, marginBottom: 20 }}>
                    {stats.map((s) => {
                        let text;
                        if (s.format === 'currency') text = `Rp${formatRupiah(s.value, 0)}`;
                        else if (s.format === 'raw') text = s.value;
                        else text = formatRupiah(s.value, s.decimals ?? 0);

                        return (
                            <div key={s.label} className="stat-card" style={{ textAlign: 'center' }}>
                                <div className="stat-label">{s.label}</div>
                                <div className="stat-value" style={{ fontSize: 21, color: s.color || 'var(--primary)' }}>{text}</div>
                                {s.unit && <div style={{ fontSize: 11, color: 'var(--text-muted)', marginTop: 4 }}>{s.unit}</div>}
                            </div>
                        );
                    })}
                </div>
            )}

            <div className="card">
                {(cardTitle || cardSubtitle) && (
                    <div style={{ marginBottom: 16 }}>
                        {cardTitle && <h3 style={{ fontSize: 15, fontWeight: 700 }}>{cardTitle}</h3>}
                        {cardSubtitle && <p style={{ fontSize: 12, color: 'var(--text-muted)', marginTop: 2 }}>{cardSubtitle}</p>}
                    </div>
                )}

                {isEmpty ? (
                    <div style={{ textAlign: 'center', padding: '32px 20px' }}>
                        <p style={{ color: 'var(--text-muted)', marginBottom: 14 }}>{emptyText}</p>
                        {canManage && createHref && (
                            <Link href={createHref} className="btn btn-sm btn-primary">+ Tambah Data Pertama</Link>
                        )}
                    </div>
                ) : children}
            </div>
        </AdminLayout>
    );
}
