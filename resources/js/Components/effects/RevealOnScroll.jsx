import { useEffect, useRef, useState } from 'react';

export default function RevealOnScroll({ as: Tag = 'div', style, className = '', children }) {
    const ref = useRef(null);
    const [visible, setVisible] = useState(false);

    useEffect(() => {
        const el = ref.current;
        if (!el) return;

        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) setVisible(true);
                });
            },
            { threshold: 0.1, rootMargin: '0px 0px -50px 0px' }
        );
        io.observe(el);

        return () => io.disconnect();
    }, []);

    return (
        <Tag ref={ref} style={style} className={`reveal ${visible ? 'visible' : ''} ${className}`.trim()}>
            {children}
        </Tag>
    );
}
