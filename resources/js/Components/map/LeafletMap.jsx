import { useEffect, useRef } from 'react';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';

import markerIcon2x from 'leaflet/dist/images/marker-icon-2x.png';
import markerIcon from 'leaflet/dist/images/marker-icon.png';
import markerShadow from 'leaflet/dist/images/marker-shadow.png';

delete L.Icon.Default.prototype._getIconUrl;
L.Icon.Default.mergeOptions({
    iconRetinaUrl: markerIcon2x,
    iconUrl: markerIcon,
    shadowUrl: markerShadow,
});

export default function LeafletMap({ center = [-2.5, 118], zoom = 5, markers = [], height = 300, style, className }) {
    const containerRef = useRef(null);
    const mapRef = useRef(null);

    useEffect(() => {
        if (!containerRef.current || mapRef.current) return;

        const map = L.map(containerRef.current).setView(center, zoom);
        mapRef.current = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        markers.forEach((m) => {
            if (m.lat == null || m.lng == null) return;
            const marker = L.marker([m.lat, m.lng]).addTo(map);
            if (m.popupHtml) marker.bindPopup(m.popupHtml);
            if (m.openPopup) marker.openPopup();
        });

        return () => {
            map.remove();
            mapRef.current = null;
        };
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    return (
        <div
            ref={containerRef}
            className={className}
            style={{ height, width: '100%', ...style }}
        />
    );
}
