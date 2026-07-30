import { useEffect, useRef, useState } from 'react';

export default function CountUp({ value, decimals = 0, prefix = '', suffix = '', as: Tag = 'div', style, className }) {
    const ref = useRef(null);
    const [display, setDisplay] = useState(prefix + Number(0).toLocaleString('id-ID') + suffix);

    useEffect(() => {
        const el = ref.current;
        if (!el) return;

        const target = Number(value) || 0;
        const duration = 2000;

        function animate() {
            const start = performance.now();
            function step(now) {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                const current = (target * eased).toFixed(decimals);
                setDisplay(prefix + Number(current).toLocaleString('id-ID') + suffix);
                if (progress < 1) requestAnimationFrame(step);
            }
            requestAnimationFrame(step);
        }

        const io = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                animate();
                io.disconnect();
            }
        });
        io.observe(el);

        return () => io.disconnect();
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [value, decimals, prefix, suffix]);

    return (
        <Tag ref={ref} style={style} className={className}>
            {display}
        </Tag>
    );
}
