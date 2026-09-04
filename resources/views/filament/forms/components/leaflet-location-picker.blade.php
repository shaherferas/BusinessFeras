<div
    x-data="{
        latitude: $wire.entangle('data.latitude').live,
        longitude: $wire.entangle('data.longitude').live,
        map: null,
        marker: null,
        async init() {
            await this.loadLeaflet();
            const lat = Number(this.latitude) || 33.5138;
            const lng = Number(this.longitude) || 36.2765;
            this.map = L.map(this.$refs.map).setView([lat, lng], this.latitude && this.longitude ? 15 : 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(this.map);
            this.setMarker(lat, lng);
            this.map.on('click', (event) => this.updateCoordinates(event.latlng.lat, event.latlng.lng));
            this.$watch('latitude', () => this.syncMarker());
            this.$watch('longitude', () => this.syncMarker());
            setTimeout(() => this.map.invalidateSize(), 200);
        },
        loadLeaflet() {
            if (window.L) return Promise.resolve();
            if (window.__leafletLoading) return window.__leafletLoading;
            window.__leafletLoading = new Promise((resolve) => {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css';
                document.head.appendChild(css);
                const script = document.createElement('script');
                script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js';
                script.onload = resolve;
                document.head.appendChild(script);
            });
            return window.__leafletLoading;
        },
        updateCoordinates(lat, lng) {
            this.latitude = Number(lat).toFixed(8);
            this.longitude = Number(lng).toFixed(8);
            this.setMarker(lat, lng);
        },
        syncMarker() {
            const lat = Number(this.latitude);
            const lng = Number(this.longitude);
            if (this.map && Number.isFinite(lat) && Number.isFinite(lng)) this.setMarker(lat, lng);
        },
        setMarker(lat, lng) {
            if (this.marker) this.marker.setLatLng([lat, lng]);
            else this.marker = L.marker([lat, lng], {draggable: true}).addTo(this.map);
            this.marker.off('dragend').on('dragend', () => {
                const point = this.marker.getLatLng();
                this.updateCoordinates(point.lat, point.lng);
            });
        }
    }"
    x-init="init()"
>
    <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">Business location</div>
    <div x-ref="map" class="h-96 w-full rounded-lg border border-gray-300 dark:border-gray-700"></div>
    <p class="mt-2 text-xs text-gray-500">Click the map or drag the marker to set latitude and longitude.</p>
</div>
