import './bootstrap';
import '../css/app.css';

import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { route } from 'ziggy-js';

// Pages call route() as a global, the way Ziggy's @routes directive used to
// provide it. Importing it here instead keeps the helper in a cached bundle
// rather than re-sending it inline with every page. It picks up the route
// table from window.Ziggy, which app.blade.php still emits.
window.route = route;

createInertiaApp({
    resolve: (name) =>
        resolvePageComponent(`./Pages/${name}.jsx`, import.meta.glob('./Pages/**/*.jsx')),
    setup({ el, App, props }) {
        createRoot(el).render(<App {...props} />);
    },
});
