@if(isset($leaflet_css))
    <link rel="stylesheet" href="{{ $leaflet_css }}">
@endif

<div id="map" style="border-radius: 5px; width: 100%; height: 800px; position: relative; t-index: 1;"></div>

@section('scripts')
    @parent
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const pilotCountries = @json($countries ?? []);
        const visitedAirports = @json($airports ?? []);
        const countryData = @json($countryData ?? []);
        const providerName = 'OpenStreetMap.Mapnik';
        const geoJsonUrl = '{{ asset('sppassport/custom.geo.json') }}';

        const map = L.map('map', {
            worldCopyJump: true,
            minZoom: 2,
            zoomSnap: 0.5,
            scrollWheelZoom: true,
        }).setView([20, 0], 2);

        try {
            if (L.tileLayer.provider) {
                L.tileLayer.provider(providerName).addTo(map);
            } else {
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; OpenStreetMap contributors',
                }).addTo(map);
            }
        } catch (e) {
            console.error('Provider-Error:', e);
        }

        const countryLayers = {};

        fetch(geoJsonUrl)
            .then(r => r.json())
            .then(data => {
                L.geoJson(data, {
                    style: feature => {
                        let iso = (feature.properties.ISO_A2 || feature.properties.iso_a2 || '').toUpperCase();
                        const adminName = (feature.properties.ADMIN || feature.properties.name || '').toLowerCase();

                        // Fix common GeoJSON ISO mismatches
                        const mapFallback = {
                            france: 'FR', norway: 'NO', denmark: 'DK', 'united kingdom': 'GB',
                            netherlands: 'NL', taiwan: 'TW', greece: 'GR', 'ivory coast': 'CI',
                            'south korea': 'KR', 'north korea': 'KP', russia: 'RU',
                            'czech republic': 'CZ', 'slovakia': 'SK', 'vatican': 'VA',
                            'kosovo': 'XK', 'laos': 'LA', 'myanmar': 'MM'
                        };
                        for (const [key, val] of Object.entries(mapFallback)) {
                            if (adminName.includes(key)) iso = val;
                        }

                        const visited = pilotCountries.includes(iso);
                        return visited
                            ? { color: '#2ecc71', weight: 1, fillColor: '#a9dfbf', fillOpacity: 0.8 }
                            : { color: 'transparent', weight: 0, fillColor: 'transparent', fillOpacity: 0 };
                    },
                    onEachFeature: (feature, layer) => {
                        let isoLower = (feature.properties.ISO_A2 || feature.properties.iso_a2 || '').toLowerCase();
                        const adminName = (feature.properties.ADMIN || feature.properties.name || '').toLowerCase();

                        const mapFallback = {
                            france: 'fr', norway: 'no', denmark: 'dk', 'united kingdom': 'gb', netherlands: 'nl', taiwan: 'tw',
                        };
                        for (const [key, val] of Object.entries(mapFallback)) {
                            if (adminName.includes(key)) isoLower = val;
                        }

                        const iso = isoLower.toUpperCase();
                        const visited = pilotCountries.includes(iso);
                        const flagUrl = `{{ asset('sppassport/flags') }}/${isoLower}.svg`;
                        const info = countryData[iso] ?? {};
                        const countryName = feature.properties.ADMIN || feature.properties.name || iso;

                        const popupHtml = `
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="flex:0 0 46px;">
                                    <img src="${flagUrl}" alt="${iso}" width="70" height="53"
                                        class="${visited ? '' : 'passport-off'}"
                                        style="border:1px solid #ccc;padding:2px;border-radius:3px;">
                                </div>
                                <div style="flex:1;">
                                    <strong>${countryName}</strong><br>
                                    <span><i class="fas fa-clock"></i> ${info.first_visit ?? '-'}</span><br>
                                    <span><i class="fas fa-plane-arrival"></i> ${info.first_airport ?? '-'}</span>
                                </div>
                            </div>`;

                        layer.bindPopup(popupHtml, { closeButton: false, offset: L.point(0, -5) });
                        countryLayers[iso] = layer;

                        layer.on({
                            mouseover() {
                                if (visited) {
                                    this.setStyle({ weight: 2, color: '#27ae60', fillOpacity: 1.0 });
                                } else {
                                    this.setStyle({ weight: 1, color: '#bdc3c7', fillColor: '#ecf0f1', fillOpacity: 0.6 });
                                }
                            },
                            mouseout() {
                                if (visited) {
                                    this.setStyle({ weight: 1, color: '#2ecc71', fillColor: '#a9dfbf', fillOpacity: 0.8 });
                                } else {
                                    this.setStyle({ color: 'transparent', weight: 0, fillColor: 'transparent', fillOpacity: 0 });
                                }
                            },
                            click(e) {
                                const popup = this.getPopup();
                                if (popup) {
                                    popup.setLatLng(e.latlng);
                                    map.openPopup(popup);
                                }
                            },
                        });
                    },
                }).addTo(map);
            })
            .catch(err => console.error('GeoJSON-Error:', err));

        const starIcon = L.icon({
            iconUrl: "{{ asset('sppassport/airport_marker.png') }}",
            iconSize: [24, 33],
            iconAnchor: [12, 16],
            popupAnchor: [0, -16],
        });

        const airportMarkers = {};

        visitedAirports.forEach(a => {
            if (!a.lat || !a.lon) return;
            const m = L.marker([a.lat, a.lon], { icon: starIcon }).addTo(map);
            m.on('click', () => {
                const iso = (a.country ?? '').toUpperCase();
                const layer = countryLayers[iso];
                if (layer?.getPopup()) {
                    const popup = layer.getPopup();
                    popup.setLatLng(m.getLatLng());
                    map.openPopup(popup);
                    layer.setStyle({ weight: 3, color: '#1abc9c', fillOpacity: 1.0 });
                    setTimeout(() => layer.setStyle({ weight: 1, color: '#2ecc71', fillOpacity: 0.8 }), 2000);
                }
            });
            airportMarkers[a.icao?.toUpperCase() ?? ''] = m;
        });

        window.sppassportMap = map;
        window.airportMarkers = airportMarkers;
        window.countryLayers = countryLayers;
    });
    </script>
@endsection