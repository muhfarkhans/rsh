<div x-data="mapX">
    <div wire:ignore id="map" style="width: 100%; height: 650px; z-index: 1"></div>
</div>

@script
<script>
    Alpine.data('mapX', () => ({
        state: @json($points),
        imgurl: '{{ $imageUrl }}',
        map: null,
        markers: [],
        init() {
            this.initializeMap();
            this.loadExistingMarkers();
        },

        initializeMap() {
            if (this.map) {
                this.map.off();
                this.map.remove();
            }

            this.map = L.map('map', {
                crs: L.CRS.Simple,
            });

            const bounds = [[0, 0], [600, 424]];
            const imageurl = '{{ url('') }}' + this.imgurl;

            L.imageOverlay(imageurl, bounds).addTo(this.map);
            this.map.fitBounds(bounds);

            // this.map.on('click', (event) => {
            //     this.addMarker(event.latlng);
            // });
        },

        loadExistingMarkers() {
            (this.state || []).forEach((coord) => {
                const closeIcon = L.icon({
                    iconUrl: '/assets/images/close.png',
                    iconSize: [15, 15],
                    popupAnchor: [-3, -76],
                });

                const marker = L.marker(coord, { icon: closeIcon })
                    .addTo(this.map)
                    .on('click', () => this.removeMarker(marker));

                this.markers.push(marker);
            });
        },

        addMarker(latlng) {
            const closeIcon = L.icon({
                iconUrl: '/assets/images/close.png',
                iconSize: [15, 15],
                popupAnchor: [-3, -76],
            });

            const marker = L.marker(latlng, { icon: closeIcon })
                .addTo(this.map)
                .on('click', () => this.removeMarker(marker));

            this.markers.push(marker);
            this.updateState();
        },

        removeMarker(marker) {
            if (confirm('Hapus titik bekam ini?')) {
                this.map.removeLayer(marker);
                let newMarkers = this.markers.filter((m) => m.getLatLng()['lat'] !== marker.getLatLng()['lat'] && m.getLatLng()['lng'] !== marker.getLatLng()['lng']);

                this.markers = newMarkers;
                this.updateState();
            }
        },

        updateState() {
            this.state = this.markers.map(marker => marker.getLatLng());
        },
    }));
</script>
@endscript