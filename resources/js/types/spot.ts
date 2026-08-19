export type Tag = {
    id: number;
    name: string;
};

export type SpotLocationData = {
    osm_place_id: number | null;
    osm_id: number | null;
    osm_type: string | null;
    osm_class: string | null;
    place_type: string | null;
    place_rank: number | null;
    importance: number | null;
    latitude: number | null;
    longitude: number | null;
    display_name: string | null;
    name: string | null;
    house_number: string | null;
    road: string | null;
    neighbourhood: string | null;
    suburb: string | null;
    city_district: string | null;
    city: string | null;
    town: string | null;
    village: string | null;
    municipality: string | null;
    county: string | null;
    state_district: string | null;
    state: string | null;
    region: string | null;
    postcode: string | null;
    country: string | null;
    country_code: string | null;
    bounding_box: unknown[] | null;
    address: Record<string, unknown> | null;
    extra_tags: Record<string, unknown> | null;
    name_details: Record<string, unknown> | null;
    geometry: Record<string, unknown> | null;
    raw_response: Record<string, unknown>;
    license: string | null;
    fetched_at: string;
};

export type Spot = {
    id: number;
    title: string;
    description: string | null;
    latitude: number | null;
    longitude: number | null;
    image_url: string;
    navigation_url: string | null;
    created_at: string;
    tags: Tag[];
    location_data: SpotLocationData | null;
};
