import { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import Pagination from '../../../Components/ui/Pagination';
import AuditDiff from '../../../Components/ui/AuditDiff';

const EVENT_BADGE = { created: 'badge-success', updated: 'badge-warning', deleted: 'badge-danger' };
const TABLES = ['projects', 'eop_data', 'tcm_data', 'cvm_data', 'benefits', 'costs'];

export default function Index({ logs, filters }) {
    const [event, setEvent] = useState(filters.event || '');
    const [tableName, setTableName] = useState(filters.table_name || '');
    const hasFilters = Boolean(filters.event || filters.table_name);

    function submitFilter(e) {
        e.preventDefault();
        router.get(route('admin.audit.index'), { event, table_name: tableName }, { preserveState: true });
    }

    return (
        <AdminLayout title="Audit Log" subtitle="Riwayat perubahan data sistem">
            <Head title="Audit Log" />

            <div className="card">
                <div style={{ display: 'flex', gap: 12, marginBottom: 20, flexWrap: 'wrap' }}>
                    <form onSubmit={submitFilter} style={{ display: 'flex', gap: 8, flexWrap: 'wrap', alignItems: 'center' }}>
                        <select value={event} onChange={(e) => setEvent(e.target.value)} className="form-input" style={{ width: 'auto', padding: '6px 30px 6px 10px', fontSize: 13 }}>
                            <option value="">Semua Event</option>
                            <option value="created">Created</option>
                            <option value="updated">Updated</option>
                            <option value="deleted">Deleted</option>
                        </select>
                        <select value={tableName} onChange={(e) => setTableName(e.target.value)} className="form-input" style={{ width: 'auto', padding: '6px 30px 6px 10px', fontSize: 13 }}>
                            <option value="">Semua Tabel</option>
                            {TABLES.map((t) => <option key={t} value={t}>{t}</option>)}
                        </select>
                        <button type="submit" className="btn btn-sm btn-outline">Filter</button>
                        {hasFilters && <Link href={route('admin.audit.index')} className="btn btn-sm btn-ghost">Reset</Link>}
                    </form>
                </div>

                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Waktu</th><th>User</th><th>Event</th><th>Tabel</th><th>ID</th><th>Detail</th></tr></thead>
                        <tbody>
                            {logs.data.length ? (
                                logs.data.map((log) => (
                                    <tr key={log.id}>
                                        <td style={{ fontSize: 12, color: 'var(--text-muted)', whiteSpace: 'nowrap' }}>
                                            {new Date(log.created_at).toLocaleString('id-ID', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}
                                        </td>
                                        <td style={{ fontSize: 13, fontWeight: 500 }}>{log.user?.name || 'System'}</td>
                                        <td><span className={`badge ${EVENT_BADGE[log.event] || 'badge-gray'}`}>{log.event}</span></td>
                                        <td style={{ fontSize: 13, fontFamily: 'monospace' }}>{log.table_name}</td>
                                        <td style={{ fontSize: 13, color: 'var(--text-muted)' }}>#{log.model_id}</td>
                                        <td><AuditDiff oldValues={log.old_values} newValues={log.new_values} /></td>
                                    </tr>
                                ))
                            ) : (
                                <tr><td colSpan={6} style={{ textAlign: 'center', color: 'var(--text-muted)', padding: 32 }}>Belum ada log audit.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
                <Pagination paginator={logs} />
            </div>
        </AdminLayout>
    );
}
