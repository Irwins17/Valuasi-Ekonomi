import { Head, Link, usePage } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';
import Pagination from '../../../Components/ui/Pagination';
import ConfirmDeleteButton from '../../../Components/ui/ConfirmDeleteButton';

const ROLE_BADGE = { admin: 'badge-primary', surveyor: 'badge-success' };

function timeAgo(dateStr) {
    if (!dateStr) return 'Belum pernah';
    const diff = (Date.now() - new Date(dateStr).getTime()) / 1000;
    const units = [
        [60, 'detik'], [60, 'menit'], [24, 'jam'], [30, 'hari'], [12, 'bulan'], [Infinity, 'tahun'],
    ];
    let value = diff;
    let unit = 'detik';
    for (const [amount, name] of units) {
        if (value < amount) { unit = name; break; }
        value /= amount;
    }
    return `${Math.floor(value)} ${unit} yang lalu`;
}

export default function Index({ users }) {
    const { auth } = usePage().props;

    return (
        <AdminLayout title="Manajemen Pengguna">
            <Head title="Manajemen Pengguna" />

            <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: 20 }} className="animate-fade-up">
                <p style={{ fontSize: 13, color: 'var(--text-muted)' }}>Kelola semua pengguna sistem</p>
                <Link href={route('admin.users.create')} className="btn btn-sm btn-primary">+ Tambah Pengguna</Link>
            </div>

            <div className="card">
                <div className="table-wrapper">
                    <table className="data-table">
                        <thead><tr><th>Nama</th><th>Email</th><th>Role</th><th>Institusi</th><th>Status</th><th>Login Terakhir</th><th style={{ textAlign: 'center' }}>Aksi</th></tr></thead>
                        <tbody>
                            {users.data.map((user) => (
                                <tr key={user.id}>
                                    <td style={{ fontWeight: 600 }}>{user.name}</td>
                                    <td style={{ fontSize: 13, color: 'var(--text-secondary)' }}>{user.email}</td>
                                    <td><span className={`badge ${ROLE_BADGE[user.role?.slug] || 'badge-purple'}`}>{user.role?.name || '-'}</span></td>
                                    <td style={{ fontSize: 13 }}>{user.institution || '-'}</td>
                                    <td><span className={`badge ${user.is_active ? 'badge-success' : 'badge-danger'}`}>{user.is_active ? 'Aktif' : 'Nonaktif'}</span></td>
                                    <td style={{ fontSize: 12, color: 'var(--text-muted)' }}>{timeAgo(user.last_login_at)}</td>
                                    <td style={{ textAlign: 'center' }}>
                                        <div style={{ display: 'flex', gap: 4, justifyContent: 'center' }}>
                                            <Link href={route('admin.users.edit', user.id)} className="btn btn-sm btn-ghost">Edit</Link>
                                            {user.id !== auth.user.id && (
                                                <ConfirmDeleteButton href={route('admin.users.destroy', user.id)} message="Hapus pengguna ini?" />
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                <Pagination paginator={users} />
            </div>
        </AdminLayout>
    );
}
