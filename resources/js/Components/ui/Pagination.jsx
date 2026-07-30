import { Link } from '@inertiajs/react';

/**
 * Accepts a raw Laravel LengthAwarePaginator object as sent by Inertia::render
 * (fields flat on the object itself: data, links, current_page, total, ...).
 */
export default function Pagination({ paginator }) {
    if (!paginator || !paginator.links || paginator.links.length <= 3) return null;

    return (
        <div className="pagination-wrapper">
            {paginator.links.map((link, i) =>
                link.url ? (
                    <Link
                        key={i}
                        href={link.url}
                        className={link.active ? 'active' : ''}
                        preserveScroll
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={i} style={{ opacity: 0.4 }} dangerouslySetInnerHTML={{ __html: link.label }} />
                )
            )}
        </div>
    );
}
