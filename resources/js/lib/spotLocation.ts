import type { SpotLocationData } from '@/types/spot';

export function formatSpotDate(value: string): string {
    return new Intl.DateTimeFormat('nl-NL', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(value));
}

export function locationLabel(location: SpotLocationData): string {
    const street = [location.road, location.house_number]
        .filter(Boolean)
        .join(' ');
    const place = location.city ?? location.town ?? location.village;
    const locality = [location.postcode, place].filter(Boolean).join(' ');

    return (
        [street, locality].filter(Boolean).join(', ') ||
        location.display_name ||
        ''
    );
}

export function locationDetailRows(
    location: SpotLocationData,
): { label: string; value: string }[] {
    const rows = [
        ['Naam', location.name],
        ['Straat', location.road],
        ['Huisnummer', location.house_number],
        ['Buurt', location.neighbourhood],
        ['Wijk', location.suburb],
        ['Stadsdeel', location.city_district],
        ['Stad', location.city],
        ['Plaats', location.town ?? location.village],
        ['Gemeente', location.municipality],
        ['Provincie', location.state],
        ['Regio', location.region ?? location.state_district],
        ['Postcode', location.postcode],
        ['Land', location.country],
        ['Landcode', location.country_code?.toUpperCase()],
        ['OSM-type', location.osm_type],
        ['Categorie', location.osm_class],
        ['Plektype', location.place_type],
        ['OSM-ID', location.osm_id?.toString()],
        ['Place-ID', location.osm_place_id?.toString()],
        ['Rang', location.place_rank?.toString()],
        ['Belangscore', location.importance?.toString()],
        [
            'Coördinaten',
            location.latitude !== null && location.longitude !== null
                ? `${location.latitude}, ${location.longitude}`
                : null,
        ],
        ['Opgehaald', new Date(location.fetched_at).toLocaleString('nl-NL')],
    ];

    return rows
        .filter((row): row is [string, string] => Boolean(row[1]))
        .map(([label, value]) => ({ label, value }));
}

export function openStreetMapUrl(location: SpotLocationData): string | null {
    if (!location.osm_type || !location.osm_id) {
        return null;
    }

    const type =
        { N: 'node', W: 'way', R: 'relation' }[
            location.osm_type.toUpperCase()
        ] ?? location.osm_type.toLowerCase();

    return `https://www.openstreetmap.org/${type}/${location.osm_id}`;
}
