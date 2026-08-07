const DEPTH_BY_TYPE = {
    Point: 0,
    MultiPoint: 1,
    LineString: 1,
    MultiLineString: 2,
    Polygon: 2,
    MultiPolygon: 3,
};

/**
 * Calls fn(lng, lat) for every coordinate pair in any GeoJSON
 * geometry/Feature/FeatureCollection, regardless of nesting depth.
 */
export function forEachCoordinate(geojson, fn) {
    function visit(coords, depth) {
        if (depth === 0) {
            const [lng, lat] = coords;
            fn(lng, lat);
            return;
        }
        coords.forEach((c) => visit(c, depth - 1));
    }

    function visitGeometry(geometry) {
        if (!geometry) return;
        const depth = DEPTH_BY_TYPE[geometry.type];
        if (depth === undefined) return;
        visit(geometry.type === 'Point' ? [geometry.coordinates] : geometry.coordinates, depth);
    }

    if (!geojson) return;
    if (geojson.type === 'FeatureCollection') {
        geojson.features.forEach((f) => visitGeometry(f.geometry));
    } else if (geojson.type === 'Feature') {
        visitGeometry(geojson.geometry);
    } else {
        visitGeometry(geojson);
    }
}

function isValidLatLng(lng, lat) {
    return Number.isFinite(lng) && Number.isFinite(lat) && Math.abs(lng) <= 180 && Math.abs(lat) <= 90;
}

/**
 * Returns the first coordinate pair that is missing, non-finite (NaN,
 * Infinity — e.g. a corrupt shapefile record), or outside valid lat/lng
 * range (e.g. a shapefile digitized in a projected CRS like UTM whose
 * .prj couldn't be reprojected). Returns null if every coordinate is a
 * plausible geographic coordinate.
 */
export function findInvalidCoordinate(geojson) {
    let bad = null;
    forEachCoordinate(geojson, (lng, lat) => {
        if (bad) return;
        if (!isValidLatLng(lng, lat)) bad = { lng, lat };
    });
    return bad;
}

/**
 * Bounding-box center [lat, lng] — used as the project's representative
 * coordinate when a boundary polygon is uploaded instead of typed in.
 * Assumes coordinates have already been validated via findInvalidCoordinate.
 */
export function boundaryCentroid(geojson) {
    let minLng = Infinity, minLat = Infinity, maxLng = -Infinity, maxLat = -Infinity;

    forEachCoordinate(geojson, (lng, lat) => {
        if (lng < minLng) minLng = lng;
        if (lng > maxLng) maxLng = lng;
        if (lat < minLat) minLat = lat;
        if (lat > maxLat) maxLat = lat;
    });

    if (!Number.isFinite(minLng) || !Number.isFinite(minLat)) return null;

    return { lat: (minLat + maxLat) / 2, lng: (minLng + maxLng) / 2 };
}

export function countFeatures(geojson) {
    if (!geojson) return 0;
    if (geojson.type === 'FeatureCollection') return geojson.features.length;
    if (geojson.type === 'Feature') return 1;
    return 1;
}

export function countCoordinates(geojson) {
    let count = 0;
    forEachCoordinate(geojson, () => { count++; });
    return count;
}
