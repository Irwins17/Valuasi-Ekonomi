import { Component } from 'react';

/**
 * Last-resort safety net for the page content area. React unmounts the
 * whole tree on an uncaught render/effect error (e.g. Leaflet throwing on
 * a malformed coordinate) and, with no boundary above it, that leaves a
 * blank white page. This catches it and shows a recoverable message
 * instead, scoped per-layout so a crash in one page can't blank the app.
 */
export default class ErrorBoundary extends Component {
    constructor(props) {
        super(props);
        this.state = { error: null };
    }

    static getDerivedStateFromError(error) {
        return { error };
    }

    componentDidCatch(error, info) {
        console.error('ErrorBoundary caught an error', error, info);
    }

    render() {
        if (this.state.error) {
            return (
                <div className="alert alert-danger" role="alert">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="12" cy="12" r="10" /><path d="M15 9l-6 6" /><path d="M9 9l6 6" /></svg>
                    <div>
                        <strong>Terjadi kesalahan saat menampilkan halaman ini.</strong>
                        <p style={{ marginTop: 4, fontSize: 13 }}>Coba muat ulang halaman. Jika masalah berlanjut, hubungi administrator.</p>
                    </div>
                </div>
            );
        }
        return this.props.children;
    }
}
