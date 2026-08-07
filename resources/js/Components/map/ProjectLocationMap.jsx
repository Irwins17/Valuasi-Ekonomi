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

const BOUNDARY_PINK = '#E8467C';
const OUTSIDE_DIM = '#5B6370';

function boundaryStyle() {
    return {
        color: BOUNDARY_PINK,
        weight: 2.5,
        opacity: 1,
        dashArray: '5, 5',
        fill: false,
        interactive: false,
    };
}

const WORLD_RING = [[-90, -180], [-90, 180], [90, 180], [90, -180]];
const maskStyle = {
    stroke: false,
    fill: true,
    fillColor: OUTSIDE_DIM,
    fillOpacity: 0.45,
    interactive: false,
};

function collectRings(geojson) {
    const rings = [];
    const addPolygon = (polygon) => {
        if (polygon?.length) {
            rings.push(polygon[0].map(([lng, lat]) => [lat, lng]));
        }
    };

    const visit = (geometry) => {
        if (!geometry) return;
        if (geometry.type === 'Polygon') {
            addPolygon(geometry.coordinates);
        } else if (geometry.type === 'MultiPolygon') {
            geometry.coordinates.forEach(addPolygon);
        } else if (geometry.type === 'GeometryCollection') {
            geometry.geometries?.forEach(visit);
        }
    };

    if (geojson.type === 'FeatureCollection') {
        geojson.features.forEach((f) => visit(f.geometry));
    } else if (geojson.type === 'Feature') {
        visit(geojson.geometry);
    } else {
        visit(geojson);
    }

    return rings;
}

export default function ProjectLocationMap({
    boundary = null,
    center = null,
    zoom = 12,
    label = 'Lokasi Survey',
    height = 260,
    style,
    className,
}) {
    const containerRef = useRef(null);
    const mapRef = useRef(null);
    const overlayRef = useRef(null);
    useEffect(() => {
        if (!containerRef.current || mapRef.current) return;
        const map = L.map(containerRef.current, { scrollWheelZoom: false, zoomSnap: 0, zoomDelta: 0.5 });
        mapRef.current = map;

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 18,
        }).addTo(map);

        return () => {
            map.remove();
            mapRef.current = null;
            overlayRef.current = null;
        };
    }, []);

    useEffect(() => {
        const map = mapRef.current;
        if (!map) return;

        if (overlayRef.current) {
            map.removeLayer(overlayRef.current);
            overlayRef.current = null;
        }

        let group = null;
        if (boundary) {
            try {
                const outline = L.geoJSON(boundary, { style: boundaryStyle });
                const rings = collectRings(boundary);
                const layers = rings.length
                    ? [L.polygon([WORLD_RING, ...rings], maskStyle), outline]
                    : [outline];

                group = L.layerGroup(layers).addTo(map);
            } catch (err) {
                console.error('ProjectLocationMap: failed to render boundary', err);
                if (group) map.removeLayer(group);
                group = null;
            }
        }

        let fitOk = false;
        if (group) {
            try {
                const bounds = L.geoJSON(boundary).getBounds();
                if (bounds.isValid()) {
                    map.fitBounds(bounds, { padding: [20, 20] });
                    fitOk = true;
                }
            } catch (err) {
                console.error('ProjectLocationMap: failed to fit boundary bounds', err);
            }
        }

        if (fitOk) {
            overlayRef.current = group;
            return;
        }

        if (group) map.removeLayer(group);

        if (center) {
            map.setView(center, zoom);
            overlayRef.current = L.marker(center).addTo(map).bindPopup(label);
        } else {
            map.setView([-2.5, 118], 5);
        }
    }, [boundary, center, zoom, label]);

    return (
        <div className={className} style={{ height, width: '100%', ...style }}>
            <div ref={containerRef} style={{ height: '100%', width: '100%' }} />
            <style>{`
                .leaflet-interactive {
                    transition: fill-opacity 180ms ease, stroke-width 180ms ease;
                }
            `}</style>
        </div>
    );
}
