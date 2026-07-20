<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Valuasi Ekonomi')</title>
    <meta name="description" content="@yield('meta_description', 'Platform transparansi data valuasi ekonomi untuk kebijakan berkelanjutan.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        :root {
            --primary: #6366f1;
            --primary-light: #818cf8;
            --primary-dark: #4f46e5;
            --primary-50: #eef2ff;
            --primary-100: #e0e7ff;
            --primary-900: #312e81;
            --secondary: #06d6a0;
            --secondary-dark: #05b384;
            --accent: #f72585;
            --accent-light: #ff5da0;
            --warning: #f59e0b;
            --danger: #ef4444;
            --success: #10b981;
            --info: #3b82f6;
            --surface: #ffffff;
            --surface-alt: #f8fafc;
            --surface-hover: #f1f5f9;
            --border: #e2e8f0;
            --border-light: #f1f5f9;
            --text: #0f172a;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
            --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
            --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
            --shadow-glow: 0 0 40px -10px rgba(99, 102, 241, 0.3);
            --radius: 12px;
            --radius-sm: 8px;
            --radius-lg: 16px;
            --radius-xl: 24px;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            color: var(--text);
            background: var(--surface-alt);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }

        /* ===== TRANSITIONS ===== */
        a, button, input, select, textarea, .card, .btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ===== ANIMATIONS ===== */
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInDown { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes fadeInLeft { from { opacity: 0; transform: translateX(-30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes fadeInRight { from { opacity: 0; transform: translateX(30px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes scaleIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes slideUp { from { opacity: 0; transform: translateY(40px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-12px); } }
        @keyframes pulse-soft { 0%, 100% { opacity: 1; } 50% { opacity: 0.7; } }
        @keyframes shimmer { 0% { background-position: -200% 0; } 100% { background-position: 200% 0; } }
        @keyframes gradientShift { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes countUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
        @keyframes slideInFromRight { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }

        .animate-fade-up { animation: fadeInUp 0.6s ease-out both; }
        .animate-fade-down { animation: fadeInDown 0.5s ease-out both; }
        .animate-fade-left { animation: fadeInLeft 0.5s ease-out both; }
        .animate-fade-right { animation: fadeInRight 0.5s ease-out both; }
        .animate-scale { animation: scaleIn 0.5s ease-out both; }
        .animate-slide-up { animation: slideUp 0.6s ease-out both; }
        .animate-float { animation: float 4s ease-in-out infinite; }
        .animate-pulse-soft { animation: pulse-soft 2s ease-in-out infinite; }
        .animate-spin { animation: spin 1s linear infinite; }
        .gradient-animated { background-size: 300% 300%; animation: gradientShift 8s ease infinite; }

        /* Stagger children */
        .stagger > * { animation: fadeInUp 0.5s ease-out both; }
        .stagger > *:nth-child(1) { animation-delay: 0.05s; }
        .stagger > *:nth-child(2) { animation-delay: 0.1s; }
        .stagger > *:nth-child(3) { animation-delay: 0.15s; }
        .stagger > *:nth-child(4) { animation-delay: 0.2s; }
        .stagger > *:nth-child(5) { animation-delay: 0.25s; }
        .stagger > *:nth-child(6) { animation-delay: 0.3s; }
        .stagger > *:nth-child(7) { animation-delay: 0.35s; }
        .stagger > *:nth-child(8) { animation-delay: 0.4s; }

        /* Scroll reveal */
        .reveal { opacity: 0; transform: translateY(30px); transition: all 0.7s cubic-bezier(0.4, 0, 0.2, 1); }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ===== PAGE TRANSITION ===== */
        .page-enter { animation: fadeInUp 0.4s ease-out; }

        /* ===== BUTTONS ===== */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 20px; border: none; border-radius: var(--radius-sm);
            font-weight: 600; font-size: 14px; font-family: inherit;
            cursor: pointer; text-decoration: none; line-height: 1.5;
            position: relative; overflow: hidden;
        }
        .btn::after {
            content: ''; position: absolute; inset: 0;
            background: linear-gradient(to right, transparent, rgba(255,255,255,0.1), transparent);
            transform: translateX(-100%); transition: transform 0.5s;
        }
        .btn:hover::after { transform: translateX(100%); }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-dark); box-shadow: 0 8px 24px rgba(99,102,241,0.35); transform: translateY(-1px); }
        .btn-secondary { background: var(--secondary); color: #fff; }
        .btn-secondary:hover { background: var(--secondary-dark); box-shadow: 0 8px 24px rgba(6,214,160,0.35); transform: translateY(-1px); }
        .btn-accent { background: var(--accent); color: #fff; }
        .btn-accent:hover { background: #d91e6f; box-shadow: 0 8px 24px rgba(247,37,133,0.35); transform: translateY(-1px); }
        .btn-outline { border: 2px solid var(--primary); color: var(--primary); background: transparent; }
        .btn-outline:hover { background: var(--primary); color: #fff; }
        .btn-outline-white { border: 2px solid rgba(255,255,255,0.5); color: #fff; background: transparent; }
        .btn-outline-white:hover { background: rgba(255,255,255,0.15); border-color: #fff; }
        .btn-ghost { background: transparent; color: var(--text-secondary); }
        .btn-ghost:hover { background: var(--surface-hover); color: var(--text); }
        .btn-danger { background: var(--danger); color: #fff; }
        .btn-danger:hover { background: #dc2626; }
        .btn-warning { background: var(--warning); color: #fff; }
        .btn-sm { padding: 6px 14px; font-size: 13px; border-radius: 6px; }
        .btn-lg { padding: 14px 28px; font-size: 16px; }
        .btn-icon { padding: 8px; width: 36px; height: 36px; }

        /* ===== CARDS ===== */
        .card {
            background: var(--surface); border-radius: var(--radius);
            padding: 24px; border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
        }
        .card:hover { box-shadow: var(--shadow-md); }
        .card-elevated { box-shadow: var(--shadow-lg); border: none; }
        .card-glass {
            background: rgba(255,255,255,0.7); backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.3); border-radius: var(--radius-lg);
        }

        /* ===== FORMS ===== */
        .form-group { margin-bottom: 20px; }
        .form-label { display: block; font-weight: 600; font-size: 14px; color: var(--text); margin-bottom: 6px; }
        .form-input {
            width: 100%; padding: 10px 14px; border: 1.5px solid var(--border);
            border-radius: var(--radius-sm); font-size: 14px; font-family: inherit;
            background: var(--surface); color: var(--text);
            transition: all 0.2s ease;
        }
        .form-input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(99,102,241,0.1); }
        .form-input::placeholder { color: var(--text-muted); }
        .form-hint { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .form-error { font-size: 12px; color: var(--danger); margin-top: 4px; }
        textarea.form-input { resize: vertical; min-height: 100px; }
        select.form-input { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%2364748b' d='M6 8L1 3h10z'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 12px center; padding-right: 36px; }

        /* ===== BADGES ===== */
        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
        .badge-primary { background: var(--primary-50); color: var(--primary-dark); }
        .badge-success { background: #d1fae5; color: #065f46; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-purple { background: #ede9fe; color: #5b21b6; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* ===== TABLE ===== */
        .table-wrapper { overflow-x: auto; border-radius: var(--radius); border: 1px solid var(--border); }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table thead { background: var(--surface-alt); }
        .data-table th { padding: 12px 16px; font-size: 13px; font-weight: 600; color: var(--text-secondary); text-align: left; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid var(--border); }
        .data-table td { padding: 14px 16px; font-size: 14px; border-bottom: 1px solid var(--border-light); }
        .data-table tbody tr { transition: background 0.15s; }
        .data-table tbody tr:hover { background: var(--primary-50); }
        .data-table tbody tr:last-child td { border-bottom: none; }

        /* ===== ALERTS ===== */
        .alert { padding: 14px 18px; border-radius: var(--radius-sm); font-size: 14px; margin-bottom: 16px; display: flex; align-items: flex-start; gap: 10px; animation: slideInFromRight 0.4s ease-out; }
        .alert-success { background: #d1fae5; color: #065f46; border-left: 4px solid var(--success); }
        .alert-danger { background: #fee2e2; color: #991b1b; border-left: 4px solid var(--danger); }
        .alert-warning { background: #fef3c7; color: #92400e; border-left: 4px solid var(--warning); }
        .alert-info { background: #dbeafe; color: #1e40af; border-left: 4px solid var(--info); }

        /* ===== UTILITIES ===== */
        .text-gradient { background: linear-gradient(135deg, var(--primary), var(--secondary)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .text-gradient-accent { background: linear-gradient(135deg, var(--primary), var(--accent)); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .bg-gradient-primary { background: linear-gradient(135deg, var(--primary), var(--primary-light)); }
        .bg-gradient-hero { background: linear-gradient(135deg, #312e81 0%, #4f46e5 40%, #06d6a0 100%); }
        .bg-gradient-mesh { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .glass { background: rgba(255,255,255,0.1); backdrop-filter: blur(20px); border: 1px solid rgba(255,255,255,0.2); }
        .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .skeleton { background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%); background-size: 200% 100%; animation: shimmer 1.5s infinite; border-radius: var(--radius-sm); }

        /* ===== PAGINATION ===== */
        .pagination-wrapper { display: flex; justify-content: center; gap: 4px; margin-top: 24px; }
        .pagination-wrapper nav span, .pagination-wrapper nav a {
            display: inline-flex; align-items: center; justify-content: center;
            min-width: 36px; height: 36px; padding: 0 8px;
            border-radius: var(--radius-sm); font-size: 14px; font-weight: 500;
            border: 1px solid var(--border); color: var(--text-secondary);
            text-decoration: none; transition: all 0.2s;
        }
        .pagination-wrapper nav a:hover { background: var(--primary-50); color: var(--primary); border-color: var(--primary); }
        .pagination-wrapper nav span[aria-current] { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* ===== STAT CARD ===== */
        .stat-card {
            background: var(--surface); border-radius: var(--radius); padding: 20px;
            border: 1px solid var(--border); position: relative; overflow: hidden;
        }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0; right: 0; height: 3px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        .stat-icon { width: 48px; height: 48px; border-radius: var(--radius); display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .stat-value { font-size: 28px; font-weight: 800; line-height: 1.2; }
        .stat-label { font-size: 13px; color: var(--text-secondary); font-weight: 500; margin-top: 2px; }

        /* ===== RESPONSIVE ===== */
        @media (max-width: 768px) {
            .hide-mobile { display: none !important; }
        }
    </style>
    @yield('styles')
</head>
<body>
    @yield('content')
    @yield('scripts')
    <script>
        // Scroll reveal
        document.addEventListener('DOMContentLoaded', () => {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('visible');
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });
            document.querySelectorAll('.reveal').forEach(el => observer.observe(el));

            // Count-up animation
            document.querySelectorAll('[data-count]').forEach(el => {
                const target = parseFloat(el.dataset.count);
                const decimals = (el.dataset.decimals || 0);
                const prefix = el.dataset.prefix || '';
                const suffix = el.dataset.suffix || '';
                const duration = 2000;
                const start = performance.now();
                const animate = (now) => {
                    const progress = Math.min((now - start) / duration, 1);
                    const eased = 1 - Math.pow(1 - progress, 3);
                    const current = (target * eased).toFixed(decimals);
                    el.textContent = prefix + Number(current).toLocaleString('id-ID') + suffix;
                    if (progress < 1) requestAnimationFrame(animate);
                };
                const io = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting) {
                        requestAnimationFrame(animate);
                        io.disconnect();
                    }
                });
                io.observe(el);
            });
        });

        // Flash message auto dismiss
        setTimeout(() => {
            document.querySelectorAll('.alert-auto-dismiss').forEach(el => {
                el.style.transition = 'all 0.5s ease';
                el.style.opacity = '0';
                el.style.transform = 'translateX(100px)';
                setTimeout(() => el.remove(), 500);
            });
        }, 4000);
    </script>
</body>
</html>
