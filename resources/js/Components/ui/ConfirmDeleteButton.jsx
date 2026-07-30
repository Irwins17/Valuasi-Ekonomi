import { router } from '@inertiajs/react';

export default function ConfirmDeleteButton({ href, message = 'Hapus?', children = 'Hapus', className = 'btn btn-sm btn-ghost', style }) {
    function handleClick() {
        if (window.confirm(message)) {
            router.delete(href);
        }
    }

    return (
        <button type="button" className={className} style={{ color: 'var(--danger)', ...style }} onClick={handleClick}>
            {children}
        </button>
    );
}
