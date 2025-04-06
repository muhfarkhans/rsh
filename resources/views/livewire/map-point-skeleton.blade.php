<div x-data="mapX">
    <div wire:ignore id="map" style="width: 100%; height: 650px; z-index: 1"></div>

    <button
        style="--c-400:var(--info-400);--c-500:var(--info-500);--c-600:var(--info-600); margin-top: 20px; margin-bottom: 20px; width: 100%"
        class="manualButton fi-btn relative grid-flow-col items-center justify-center font-semibold outline-none transition duration-75 focus-visible:ring-2 rounded-lg fi-color-custom fi-btn-color-info fi-color-info fi-size-md fi-btn-size-md gap-1.5 px-3 py-2 text-sm inline-grid shadow-sm bg-custom-600 text-white hover:bg-custom-500 focus-visible:ring-custom-500/50 dark:bg-custom-500 dark:hover:bg-custom-400 dark:focus-visible:ring-custom-400/50 fi-ac-action fi-ac-btn-action"
        @click="manualPrint()">Generate PDF</button>
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
                zoomControl: false
            });

            this.map.dragging.disable();
            this.map.touchZoom.disable();
            this.map.scrollWheelZoom.disable();
            this.map.doubleClickZoom.disable();

            const bounds = [[0, 0], [600, 424]];
            const imageurl = '{{ url('') }}' + this.imgurl;

            L.imageOverlay(imageurl, bounds).addTo(this.map);
            this.map.fitBounds(bounds);
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
                this.markers = this.markers.filter((m) =>
                    m.getLatLng().lat !== marker.getLatLng().lat &&
                    m.getLatLng().lng !== marker.getLatLng().lng
                );
                this.updateState();
            }
        },

        updateState() {
            this.state = this.markers.map(marker => marker.getLatLng());
        },

        manualPrint() {
            console.log('{{ $filePdfname }}');
            console.log({{ $id }});

            html2canvas(document.getElementById("map"), {
                // allowTaint: true,
                // useCORS: true,
            })
                .then(function (canvas) {
                    try {
                        let image = canvas.toDataURL("image/png", 0.5);
                        fetch("{{ url('/pdf/detail') }}",
                            {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.head.querySelector('meta[name=csrf-token]').content
                                },
                                body: JSON.stringify({
                                    id: {{ $id }},
                                    map_base64: image
                                })
                            }
                        )
                            .then(response => {
                                if (response.ok) {
                                    return response.blob();
                                }
                                throw new Error('Failed to download PDF');
                            })
                            .then(blob => {
                                let url = window.URL.createObjectURL(blob);
                                let a = document.createElement('a');
                                a.href = url;
                                a.download = '{{ $filePdfname }}' + '.pdf';
                                a.click();
                                window.URL.revokeObjectURL(url);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                            });

                        console.log("image: ", image);
                    } catch (error) {
                        console.log("error: ", error);
                    }
                })
                .catch((e) => {
                    console.log(e);
                });
        },
    }));
</script>
@endscript