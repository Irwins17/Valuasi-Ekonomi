import { useRef } from 'react';
import { useForm } from '@inertiajs/react';

export default function ImportExportBar({ exportHref, importHref }) {
    const fileInputRef = useRef(null);
    const { setData, post, processing, errors, reset } = useForm({ file: null });

    function submitImport(e) {
        e.preventDefault();
        post(importHref, {
            forceFormData: true,
            onSuccess: () => {
                reset();
                if (fileInputRef.current) fileInputRef.current.value = '';
            },
        });
    }

    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
            <a href={exportHref} className="btn btn-sm btn-outline">⬇ Export Excel</a>
            <form onSubmit={submitImport} style={{ display: 'flex', alignItems: 'center', gap: 6 }}>
                <input
                    ref={fileInputRef}
                    type="file"
                    accept=".xlsx,.csv,.txt"
                    onChange={(e) => setData('file', e.target.files[0] ?? null)}
                    style={{ fontSize: 12 }}
                />
                <button type="submit" className="btn btn-sm btn-ghost" disabled={processing}>Import</button>
            </form>
            {errors.file && <span className="form-error">{errors.file}</span>}
        </div>
    );
}
