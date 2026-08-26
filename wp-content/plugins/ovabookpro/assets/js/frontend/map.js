(function ($) {
    OBP_Frontend_Map = {
        init: function() {

            if( map_object.map_platform != 'google_map' && map_object.enable_map == 'yes' ) {
                this.map_business();
            }
        },

        map_business: function() { // OSM map
            async function obp_reverse_geocoding(lat = '', lng = '' ){
                const url = 'https://nominatim.openstreetmap.org/reverse?format=jsonv2&lat='+lat+'&lon='+lng;
                const response = await fetch(url, {
                    method: "GET",
                    headers: {
                        "Content-Type": "application/json",
                    },
                }).then(response => {
                    if (!response.ok) {
                        return response.json()
                            .catch(() => {
                                // Couldn't parse the JSON
                                throw new Error(response.status);
                            })
                            .then(({message}) => {
                                // Got valid JSON with error response, use it
                                throw new Error(message || response.status);
                            });
                    }
                    // Successful response, parse the JSON and return the data
                    return response.json();
                });;
                return response;
            }

            let config = {
                minZoom: 7,
                maxZoom: 18,
            };

            const zoom = 13;

            var lat = parseFloat( $('.obp_map input[name="map_latitude"]').val() );
            var lng = parseFloat( $('.obp_map input[name="map_longitude"]').val() );

            if ( isNaN( lat ) ) {
                lat = parseFloat( '40.730610' );
            }

            if ( isNaN( lng ) ) {
                lng = parseFloat( '-73.935242' );
            }

            const map = L.map("obp_enable_map", config).setView([lat, lng], zoom);

            L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
                attribution:
                '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            }).addTo(map);

            var marker = new L.marker([lat, lng]);
            map.addLayer(marker);

            if ( $('.auto-search-wrapper').length > 0 ) {
                var marker = new L.marker([lat, lng],{draggable:'true'});
                function onMapClick(e) {

                    marker.on('dragend', async function(event){
                        var marker = event.target;
                        var position = marker.getLatLng();
                        
                        marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
                        map.panTo(new L.LatLng(position.lat, position.lng));

                        $('.obp_map input[name="map_latitude"]').val( position.lat );
                        $('.obp_map input[name="map_longitude"]').val( position.lng );

                        const reverse_geocoding = await obp_reverse_geocoding( position.lat, position.lng );

                        const display_name = reverse_geocoding?.display_name;
                        const address = reverse_geocoding?.address;
                        const city = address?.city;
                        const country_code = address?.country_code;
                        const state = address?.state;
                        const postcode = address?.postcode;

                        $('.obp_map input[name="country_code"]').val( country_code );
                        $('.obp_map input[name="city"]').val( city );
                        $('.obp_map input[name="full_address"]').val( display_name );
                        $('.obp_map input[name="state"]').val( state );
                        $('.obp_map input[name="postcode"]').val( postcode );
                    });

                    map.addLayer(marker);
                };

                map.on('click', onMapClick);

            
                new Autocomplete("search", {
                    delay: 1000,
                    selectFirst: true,
                    howManyCharacters: 2,

                    onSearch: function ({ currentValue }) {
                        const api = `https://nominatim.openstreetmap.org/search?format=geojson&limit=5&q=${encodeURI(
                            currentValue
                            )}`;

                    /**
                    * Promise
                    */
                        return new Promise((resolve) => {
                            fetch(api)
                            .then((response) => response.json())
                            .then((data) => {
                                resolve(data.features);
                            })
                            .catch((error) => {
                                console.error(error);
                            });
                        });
                    },
                    // nominatim
                    onResults: ({ currentValue, matches, template }) => {
                        const regex = new RegExp(currentValue, "i");
                        // checking if we have results if we don't
                        // take data from the noResults method
                        return matches === 0
                        ? template
                        : matches
                        .map((element) => {
                            return `
                            <li class="loupe" role="option">
                            ${element.properties.display_name.replace(
                                regex,
                                (str) => `<b>${str}</b>`
                                )}
                            </li> `;
                        })
                        .join("");
                    },

                    onSubmit: async function({ object }){
                        const { display_name } = object.properties;
                        const cord = object.geometry.coordinates;
                        // custom id for marker
                        const customId = Math.random();

                        const marker = L.marker([cord[1], cord[0]], {
                            title: display_name,
                            id: customId,
                        });

                        marker.addTo(map).bindPopup(display_name);

                        map.setView([cord[1], cord[0]], 13);

                        $('.obp_map input[name="map_latitude"]').val( cord[1] );
                        $('.obp_map input[name="map_longitude"]').val( cord[0] );
                        $('.obp_map input[name="full_address"]').val( display_name );

                        const reverse_geocoding = await obp_reverse_geocoding( cord[1], cord[0] );
    
                        const address = reverse_geocoding?.address;
                        const city = address?.city;
                        const country_code = address?.country_code;
                        const state = address?.state;
                        const postcode = address?.postcode;

                        $('.obp_map input[name="country_code"]').val( country_code );
                        $('.obp_map input[name="city"]').val( city );
                        $('.obp_map input[name="state"]').val( state );
                        $('.obp_map input[name="postcode"]').val( postcode );

                        map.eachLayer(function (layer) {
                            if (layer.options && layer.options.pane === "markerPane") {
                                if (layer.options.id !== customId) {
                                    map.removeLayer(layer);
                                }
                            }
                        });

                        marker.on("click", (e) => {
                            e.target.dragging.enable();
                            
                            marker.on('dragend', async function(event){
                                var marker = event.target;
                                var position = marker.getLatLng();
                                marker.setLatLng(new L.LatLng(position.lat, position.lng),{draggable:'true'});
                                map.panTo(new L.LatLng(position.lat, position.lng));

                                $('.obp_map input[name="map_latitude"]').val( position.lat );
                                $('.obp_map input[name="map_longitude"]').val( position.lng );

                                const reverse_geocoding = await obp_reverse_geocoding( position.lat, position.lng );

                                const display_name = reverse_geocoding?.display_name;
                                const address = reverse_geocoding?.address;
                                const city = address?.city;
                                const country_code = address?.country_code;
                                const state = address?.state;
                                const postcode = address?.postcode;

                                $('.obp_map input[name="country_code"]').val( country_code );
                                $('.obp_map input[name="city"]').val( city );
                                $('.obp_map input[name="full_address"]').val( display_name );
                                $('.obp_map input[name="state"]').val( state );
                                $('.obp_map input[name="postcode"]').val( postcode );
                            });
                        });
                    },

                    // get index and data from li element after
                    // hovering over li with the mouse or using
                    // arrow keys ↓ | ↑
                    onSelectedItem: ({ index, element, object }) => {
                        map.on('click', onMapClick);
                    },

                    // the method presents no results
                    noResults: ({ currentValue, template }) =>
                    template(`<li>No results found: "${currentValue}"</li>`),
                });
            }
        },

        };

        $(document).ready( function () {
            OBP_Frontend_Map.init();
        });

})(jQuery);