import { Head, Link, useForm } from '@inertiajs/react';
import AdminLayout from '../../../Layouts/AdminLayout';

export default function Create({ roles }) {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        role_id: roles[0]?.id ?? '',
        phone: '',
        institution: '',
    });

    function submit(e) {
        e.preventDefault();
        post(route('admin.users.store'));
    }

    return (
        <AdminLayout title="Tambah Pengguna">
            <Head title="Tambah Pengguna" />

            <div style={{ maxWidth: 600, margin: '0 auto' }} className="animate-fade-up">
                <Link href={route('admin.users.index')} style={{ color: 'var(--text-muted)', textDecoration: 'none', fontSize: 13, display: 'block', marginBottom: 16 }}>← Kembali</Link>
                <div className="card">
                    <form onSubmit={submit}>
                        <div className="form-group">
                            <label className="form-label">Nama</label>
                            <input type="text" value={data.name} onChange={(e) => setData('name', e.target.value)} required className="form-input" />
                            {errors.name && <p className="form-error">{errors.name}</p>}
                        </div>
                        <div className="form-group">
                            <label className="form-label">Email</label>
                            <input type="email" value={data.email} onChange={(e) => setData('email', e.target.value)} required className="form-input" />
                            {errors.email && <p className="form-error">{errors.email}</p>}
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Password</label>
                                <input type="password" value={data.password} onChange={(e) => setData('password', e.target.value)} required className="form-input" />
                                {errors.password && <p className="form-error">{errors.password}</p>}
                            </div>
                            <div className="form-group">
                                <label className="form-label">Konfirmasi Password</label>
                                <input type="password" value={data.password_confirmation} onChange={(e) => setData('password_confirmation', e.target.value)} required className="form-input" />
                            </div>
                        </div>
                        <div className="form-group">
                            <label className="form-label">Role</label>
                            <select value={data.role_id} onChange={(e) => setData('role_id', e.target.value)} className="form-input" required>
                                {roles.map((role) => <option key={role.id} value={role.id}>{role.name}</option>)}
                            </select>
                        </div>
                        <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                            <div className="form-group">
                                <label className="form-label">Telepon</label>
                                <input type="text" value={data.phone} onChange={(e) => setData('phone', e.target.value)} className="form-input" />
                            </div>
                            <div className="form-group">
                                <label className="form-label">Institusi</label>
                                <input type="text" value={data.institution} onChange={(e) => setData('institution', e.target.value)} className="form-input" />
                            </div>
                        </div>
                        <div style={{ display: 'flex', gap: 10, paddingTop: 20, borderTop: '1px solid var(--border-light)' }}>
                            <button type="submit" className="btn btn-primary" disabled={processing}>Simpan</button>
                            <Link href={route('admin.users.index')} className="btn btn-ghost">Batal</Link>
                        </div>
                    </form>
                </div>
            </div>
        </AdminLayout>
    );
}
