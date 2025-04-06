<div id="map-container">
    <div id="map" style="width: 100%; height: 650px; z-index: 1"></div>
    <button id="manualButton">Manual print</button>
</div>

<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script>
    const state = [{ "lat": 450, "lng": 54 }, { "lat": 128, "lng": 402 }];
    const imgurl = '{{ $imageUrl }}';
    let map = null;
    let markers = [];

    function initializeMap() {

        console.log('asasasas');

        if (map) {
            map.off();
            map.remove();
        }

        map = L.map('map', {
            crs: L.CRS.Simple,
            zoomControl: false
        });

        const imageurl = '{{ url('') }}' + imgurl;
        console.log('Image URL:', imageurl);  // Debugging step

        const image = new Image();
        image.src = imageurl;

        image.onload = () => {
            const width = image.width;
            const height = image.height;
            const bounds = [[0, 0], [height, width]];  // Adjust bounds to image size
            L.imageOverlay(imageurl, bounds).addTo(map);
            map.fitBounds(bounds);
        };

        image.onerror = () => {
            console.error('Failed to load image:', imageurl);
        };

        console.log('sfgsgsfg');

    }

    function loadExistingMarkers() {
        (state || []).forEach((coord) => {
            const closeIcon = L.icon({
                iconUrl: '/assets/images/close.png',
                iconSize: [15, 15],
                popupAnchor: [-3, -76],
            });

            const marker = L.marker(coord, { icon: closeIcon })
                .addTo(map)
                .on('click', () => removeMarker(marker));

            markers.push(marker);
        });
    }

    function addMarker(latlng) {
        const closeIcon = L.icon({
            iconUrl: '/assets/images/close.png',
            iconSize: [15, 15],
            popupAnchor: [-3, -76],
        });

        const marker = L.marker(latlng, { icon: closeIcon })
            .addTo(map)
            .on('click', () => removeMarker(marker));

        markers.push(marker);
        updateState();
    }

    function removeMarker(marker) {
        if (confirm('Hapus titik bekam ini?')) {
            map.removeLayer(marker);
            markers = markers.filter((m) =>
                m.getLatLng().lat !== marker.getLatLng().lat &&
                m.getLatLng().lng !== marker.getLatLng().lng
            );
            updateState();
        }
    }

    function updateState() {
        state.length = 0;
        markers.forEach(marker => state.push(marker.getLatLng()));
    }

    function manualPrint() {
        console.log("testtt");

        html2canvas(document.getElementById("map"))
            .then(function (canvas) {
                try {
                    console.log("canvas: ", canvas);
                    let image = canvas.toDataURL("image/png", 0.5);
                    console.log("image: ", image);
                } catch (error) {
                    console.log("error: ", error);
                }
            })
            .catch((e) => {
                console.log(e);
            });
    }

    // Initialize the map and load markers
    initializeMap();
    loadExistingMarkers();

    // Event listener for the manual print button
    document.getElementById('manualButton').addEventListener('click', manualPrint);
</script>