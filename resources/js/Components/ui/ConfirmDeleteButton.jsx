import { useState } from 'react';
import { router } from '@inertiajs/react';

export default function ConfirmDeleteButton({
    href,
    message = 'Hapus?',
    children = 'Hapus',
    className = 'btn btn-sm btn-ghost',
    style,
}) {
    const [processing, setProcessing] = useState(false);

    function handleClick() {
        // The row stays on screen until the redirect lands, so without this
        // guard an impatient second click fires a second DELETE for a record
        // that is already gone.
        if (processing) return;
        if (!window.confirm(message)) return;

        setProcessing(true);
        router.delete(href, {
            preserveScroll: true,
            onFinish: () => setProcessing(false),
        });
    }

    return (
        <button
            type="button"
            className={className}
            disabled={processing}
            style={{ color: 'var(--danger)', opacity: processing ? 0.6 : 1, ...style }}
            onClick={handleClick}
        >
            {processing ? 'Menghapus…' : children}
        </button>
    );
}
