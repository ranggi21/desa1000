let baseUrl = '';
let currentUrl = '';
let currentLat = 0, currentLng = 0
let userLat = 0, userLng = 0;
let web, map;
let infoWindow = new google.maps.InfoWindow();
let userInfoWindow = new google.maps.InfoWindow();
let directionsService, directionsRenderer;
let userMarker = new google.maps.Marker();
let destinationMarker = new google.maps.Marker();
let routeArray = [], circleArray = [], markerArray = {};
let bounds = new google.maps.LatLngBounds();
let selectedShape, drawingManager = new google.maps.drawing.DrawingManager();

let customStyled = [
    {
        "elementType": "labels",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "administrative.land_parcel",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "administrative.neighborhood",
        "stylers": [
            {
                "visibility": "off"
            }
        ]
    },
    {
        "featureType": "road",
        "elementType": "labels",
        "stylers": [
            {
                "visibility": "on"
            }
        ]
    }
];

function setBaseUrl(url) {
    baseUrl = url;
}

// Initialize and add the map
function initMap(lat = -0.5242972, lng = 100.492333, mobile = false) {
    directionsService = new google.maps.DirectionsService();
    const center = new google.maps.LatLng(lat, lng);
    if (!mobile) {
        map = new google.maps.Map(document.getElementById("googlemaps"), {
            zoom: 7,
            center: center,
            mapTypeId: 'roadmap',
        });
    } else {
        map = new google.maps.Map(document.getElementById("googlemaps"), {
            zoom: 18,
            center: center,
            mapTypeControl: false,
        });
    }
    var rendererOptions = {
        map: map
    }
    map.set('styles', customStyled);
    directionsRenderer = new google.maps.DirectionsRenderer(rendererOptions);
    digitVillage();
    digitRegion();
}

// Display tourism village digitizing
function digitVillage() {
    const village = new google.maps.Data();
    $.ajax({
        url: baseUrl + '/api/village',
        type: 'POST',
        data: {
            village: '1'
        },
        dataType: 'json',
        success: function (response) {
            const data = response.data;
            console.log(data)
            village.addGeoJson(data);
            village.setStyle({
                fillColor:'#00b300',
                strokeWeight:0.5,
                strokeColor:'#005000',
                fillOpacity: 0.1,
                clickable: false
            });
            village.setMap(map);
        }
    });
}
function digitRegion() {
    const a = { type: 'Feature', geometry: regionData }
    const region = new google.maps.Data();
    region.addGeoJson(a)
    region.setStyle({
      fillColor:'#00b300',
      strokeWeight:0.5,
      strokeColor:'#005000',
      fillOpacity: 0.1,
       clickable: false
    });
    region.setMap(map);
}


// Remove user location
function clearUser() {
    userLat = 0;
    userLng = 0;
    userMarker.setMap(null);
}

// Set current location based on user location
function setUserLoc(lat, lng) {
    userLat = lat;
    userLng = lng;
    currentLat = userLat;
    currentLng = userLng;
}

// Remove any route shown
function clearRoute() {
    for(i in routeArray) {
        routeArray[i].setMap(null);
    }
    routeArray = [];
    $('#direction-row').hide();
}

// Remove any radius shown
function clearRadius() {
    for (i in circleArray) {
        circleArray[i].setMap(null);
    }
    circleArray = [];
}

// Remove any marker shown
function clearMarker() {
    for (i in markerArray) {
        markerArray[i].setMap(null);
    }
    markerArray = {};
}

// Get user's current position
function currentPosition() {
    clearRadius();
    clearRoute();

    google.maps.event.clearListeners(map, 'click');
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition((position) => {
                const pos = {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                };

                infoWindow.close();
                clearUser();
                markerOption = {
                    position: pos,
                    animation: google.maps.Animation.DROP,
                    map: map,
                };
                userMarker.setOptions(markerOption);
                userInfoWindow.setContent("<p class='text-center'><span class='fw-bold'>You are here.</span> <br> lat: " + pos.lat + "<br>long: " + pos.lng+ "</p>");
                userInfoWindow.open(map, userMarker);
                map.setCenter(pos);
                setUserLoc(pos.lat, pos.lng);

                userMarker.addListener('click', () => {
                    userInfoWindow.open(map, userMarker);
                });
            },
            () => {
                handleLocationError(true, userInfoWindow, map.getCenter());
            }
        );
    } else {
        // Browser doesn't support Geolocation
        handleLocationError(false, userInfoWindow, map.getCenter());
    }
}

// Error handler for geolocation
function handleLocationError(browserHasGeolocation, infoWindow, pos) {
    infoWindow.setPosition(pos);
    infoWindow.setContent(
        browserHasGeolocation
            ? "Error: The Geolocation service failed."
            : "Error: Your browser doesn't support geolocation."
    );
    infoWindow.open(map);
}

// User set position on map
function manualPosition() {

    clearRadius();
    clearRoute();

    if (userLat == 0 && userLng == 0) {
        Swal.fire('Click on Map');
    }
    map.addListener('click', (mapsMouseEvent) => {

        infoWindow.close();
        pos = mapsMouseEvent.latLng;

        clearUser();
        markerOption = {
            position: pos,
            animation: google.maps.Animation.DROP,
            map: map,
        };
        userMarker.setOptions(markerOption);
        userInfoWindow.setContent("<p class='text-center'><span class='fw-bold'>You are here.</span> <br> lat: " + pos.lat().toFixed(8) + "<br>long: " + pos.lng().toFixed(8)+ "</p>");
        userInfoWindow.open(map, userMarker);

        userMarker.addListener('click', () => {
            userInfoWindow.open(map, userMarker);
        });

        setUserLoc(pos.lat().toFixed(8), pos.lng().toFixed(8))
    });

}

// Render route on selected object
function routeTo(lat, lng, routeFromUser = true) {

    clearRadius();
    clearRoute();
    google.maps.event.clearListeners(map, 'click')

    let start, end;
    if (routeFromUser) {
        if (userLat == 0 && userLng == 0) {
            return Swal.fire('Determine your position first!');
        }
        setUserLoc(userLat, userLng);
    }
    start = new google.maps.LatLng(currentLat, currentLng);
    end = new google.maps.LatLng(lat, lng)
    let request = {
        origin: start,
        destination: end,
        travelMode: 'DRIVING'
    };
    directionsService.route(request, function(result, status) {
        if (status == 'OK') {
            directionsRenderer.setDirections(result);
            showSteps(result);
            directionsRenderer.setMap(map);
            routeArray.push(directionsRenderer);
        }
    });
    boundToRoute(start, end);
}

// Display marker for loaded object
function objectMarker(id, lat, lng, anim = true) {

    google.maps.event.clearListeners(map, 'click');
    let pos = new google.maps.LatLng(lat, lng);
    let marker = new google.maps.Marker();

    let icon;
    if (id.substring(0,1) === "R") {
        icon = baseUrl + '/media/icon/marker_rg.png';
    } else if (id.substring(0,1) === "C") {
        icon = baseUrl + '/media/icon/marker_cp.png';
    } else if (id.substring(0,1) === "W") {
        icon = baseUrl + '/media/icon/marker_wp.png';
    } else if (id.substring(0,1) === "S") {
        icon = baseUrl + '/media/icon/marker_sp.png';
    } else if (id.substring(0,1) === "E") {
        icon = baseUrl + '/media/icon/marker_ev.png';
    }

    markerOption = {
        position: pos,
        icon: icon,
        animation: google.maps.Animation.DROP,
        map: map,
    }
    marker.setOptions(markerOption);
    if (!anim) {
        marker.setAnimation(null);
    }
    marker.addListener('click', () => {
        infoWindow.close();
        objectInfoWindow(id);
        infoWindow.open(map, marker);
    });
    markerArray[id] = marker;
}

// Display markers for loaded objects from package
function objectPackageDetail(objectData) {
    objectData.forEach(element => {
        let id = element['id']
        let lat = element['lat']
        let lng = element['lng']
        
        objectMarker(id,lat,lng,anim=false)
    });
}

// Display info window for loaded object
function objectInfoWindow(id){
    let content = '';
    let contentButton = '';
    let contentMobile = '';

    if (id.substring(0,1) === "R") {
        $.ajax({
            url: baseUrl + '/api/rumahGadang/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let rgid = data.id;
                let name = data.name;
                let lat = data.lat;
                let lng = data.lng;
                let ticket_price = (data.ticket_price == 0) ? 'Free' : 'Rp ' + data.ticket_price;
                let open = data.open.substring(0, data.open.length - 3);
                let close = data.close.substring(0, data.close.length - 3);

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p> <br>' +
                    '<p><i class="fa-solid fa-clock me-2"></i> '+open+' - '+close+' WIB</p>' +
                    '<p><i class="fa-solid fa-money-bill me-2"></i> '+ ticket_price +'</p>' +
                    '</div>';
                contentButton =
                    '<br><div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '<a title="Info" class="btn icon btn-outline-primary mx-1" target="_blank" id="infoInfoWindow" href='+baseUrl+'/web/rumahGadang/'+rgid+'><i class="fa-solid fa-info"></i></a>' +
                    '<a title="Nearby" class="btn icon btn-outline-primary mx-1" id="nearbyInfoWindow" onclick="openNearby(`'+ rgid +'`,'+ lat +','+ lng +')"><i class="fa-solid fa-compass"></i></a>' +
                    '</div>'
                contentMobile =
                    '<br><div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '</div>'

                if (currentUrl.includes(id)) {
                    if (currentUrl.includes('mobile')){
                        infoWindow.setContent(content + contentMobile);
                    } else {
                        infoWindow.setContent(content);
                    }
                    infoWindow.open(map, markerArray[rgid])
                } else {
                    infoWindow.setContent(content + contentButton);
                }
            }
        });
    } else if (id.substring(0,1) === "U") {
        $.ajax({
            url: baseUrl + '/api/uniquePlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let upid = data.id;
                let name = data.name;
                let lat = data.lat;
                let lng = data.lng;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p> <br>' +
                    '</div>';
                contentButton =
                    '<br><div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '<a title="Info" class="btn icon btn-outline-primary mx-1" target="_blank" id="infoInfoWindow" href='+baseUrl+'/web/uniquePlace/'+upid+'><i class="fa-solid fa-info"></i></a>' +
                    '<a title="Nearby" class="btn icon btn-outline-primary mx-1" id="nearbyInfoWindow" onclick="openNearby(`'+ upid +'`,'+ lat +','+ lng +')"><i class="fa-solid fa-compass"></i></a>' +
                    '</div>'
                contentMobile =
                    '<br><div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '</div>'

                if (currentUrl.includes(id)) {
                    if (currentUrl.includes('mobile')){
                        infoWindow.setContent(content + contentMobile);
                    } else {
                        infoWindow.setContent(content);
                    }
                    infoWindow.open(map, markerArray[upid])
                } else {
                    infoWindow.setContent(content + contentButton);
                }
            }
        });
    } else if (id.substring(0,1) === "E") {
        const months = [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December'
        ];
        $.ajax({
            url: baseUrl + '/api/event/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let evid = data.id;
                let name = data.name;
                let lat = data.lat;
                let lng = data.lng;
                let ticket_price = (data.ticket_price == 0) ? 'Free' : 'Rp ' + data.ticket_price;
                let category = data.category;
                let date_next = new Date(data.date_start);
                let next = date_next.getDate() + ' ' + months[date_next.getMonth()] + ' ' + date_next.getFullYear();

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p> <br>' +
                    '<p><i class="fa-solid fa-layer-group me-2"></i> '+ category +'</p>' +
                    '<p><i class="fa-solid fa-money-bill me-2"></i> '+ ticket_price +'</p>' +
                    '<p><i class="fa-solid fa-calendar-days me-2"></i> '+ next +'</p>' +
                    '</div>';
                contentButton =
                    '<br><div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '<a title="Info" class="btn icon btn-outline-primary mx-1" target="_blank" id="infoInfoWindow" href='+baseUrl+'/web/event/'+evid+'><i class="fa-solid fa-info"></i></a>' +
                    '<a title="Nearby" class="btn icon btn-outline-primary mx-1" id="nearbyInfoWindow" onclick="openNearby(`'+ evid +'`,'+ lat +','+ lng +')"><i class="fa-solid fa-compass"></i></a>' +
                    '</div>'
                contentMobile =
                    '<br><div class="text-center">' +
                    '<a title="Route" class="btn icon btn-outline-primary mx-1" id="routeInfoWindow" onclick="routeTo('+lat+', '+lng+')"><i class="fa-solid fa-road"></i></a>' +
                    '</div>'

                if (currentUrl.includes(id)) {
                    if (currentUrl.includes('mobile')){
                        infoWindow.setContent(content + contentMobile);
                    } else {
                        infoWindow.setContent(content);
                    }
                    infoWindow.open(map, markerArray[evid])
                } else {
                    infoWindow.setContent(content + contentButton);
                }
            }
        });
    } else if (id.substring(0,1) === "C") {
        $.ajax({
            url: baseUrl + '/api/culinaryPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let name = data.name;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p>' +
                    '</div>';

                infoWindow.setContent(content);
            }
        });
    } else if (id.substring(0,1) === "W") {
        $.ajax({
            url: baseUrl + '/api/worshipPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let name = data.name;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p>' +
                    '</div>';

                infoWindow.setContent(content);
            }
        });
    } else if (id.substring(0,1) === "S") {
        $.ajax({
            url: baseUrl + '/api/souvenirPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                let name = data.name;

                content =
                    '<div class="text-center">' +
                    '<p class="fw-bold fs-6">'+ name +'</p>' +
                    '</div>';

                infoWindow.setContent(content);
            }
        });
    }
}

// Render map to contains all object marker
function boundToObject(firstTime = true) {
    if (Object.keys(markerArray).length > 0) {
        bounds = new google.maps.LatLngBounds();
        for (i in markerArray) {
            bounds.extend(markerArray[i].getPosition())
        }
        if (firstTime) {
            map.fitBounds(bounds, 80);
        } else {
            map.panTo(bounds.getCenter());
        }
    } else {
        let pos = new google.maps.LatLng(-0.5242972, 100.492333);
        map.panTo(pos);
    }
}

// Render map to contains route and its markers
function boundToRoute(start, end) {
    bounds = new google.maps.LatLngBounds();
    bounds.extend(start);
    bounds.extend(end);
    map.panToBounds(bounds, 100);
}

// Add user position to map bound
function boundToRadius(lat, lng, rad) {
    let userBound = new google.maps.LatLng(lat, lng);
    const radiusCircle = new google.maps.Circle({
        center: userBound,
        radius: Number(rad)
    });
    map.fitBounds(radiusCircle.getBounds());
}

// Draw radius circle
function drawRadius(position, radius) {
    const radiusCircle = new google.maps.Circle({
        center: position,
        radius: radius,
        map: map,
        strokeColor: "#FF0000",
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: "#FF0000",
        fillOpacity: 0.35,
    });
    circleArray.push(radiusCircle);
    boundToRadius(currentLat, currentLng, radius);
}

// Update radiusValue on search by radius
function updateRadius(postfix) {
    document.getElementById('radiusValue' + postfix).innerHTML = (document.getElementById('inputRadius' + postfix).value * 100) + " m";
}

// Render search by radius
function radiusSearch({postfix= null, } = {}) {

    if (userLat == 0 && userLng == 0) {
        document.getElementById('radiusValue' + postfix).innerHTML = "0 m";
        document.getElementById('inputRadius' + postfix).value = 0;
        return Swal.fire('Determine your position first!');
    }

    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    closeNearby();

    let pos = new google.maps.LatLng(currentLat, currentLng);
    let radiusValue = parseFloat(document.getElementById('inputRadius' + postfix).value) * 100;
    map.panTo(pos);

    // find object in radius
    if (postfix === 'RG') {
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radiusValue
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                drawRadius(pos, radiusValue);
            }
        });
    } else if (postfix === 'EV') {
        $.ajax({
            url: baseUrl + '/api/event/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radiusValue
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                drawRadius(pos, radiusValue);
            }
        });
    } else if (postfix === 'UP') {
        $.ajax({
            url: baseUrl + '/api/uniquePlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radiusValue
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                drawRadius(pos, radiusValue);
            }
        });
    }

}

// pan to selected object
function focusObject(id) {
    google.maps.event.trigger(markerArray[id], 'click');
    map.panTo(markerArray[id].getPosition())
}

// display objects by feature used
function displayFoundObject(response) {
    $('#table-data').empty();
    let data = response.data;
    let counter = 1;
    const months = [
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    ];
    for (i in data) {
        let item = data[i];
        let row;
        if (item.hasOwnProperty('date_next')){
            let date_next = new Date(item.date_start);
            let next = date_next.getDate() + ' ' + months[date_next.getMonth()] + ' ' + date_next.getFullYear();
            row =
                '<tr>'+
                '<td>'+ counter +'</td>' +
                '<td class="fw-bold">'+ item.name +'<br><span class="text-muted">' + next + '</span></td>' +
                '<td>'+
                '<a data-bs-toggle="tooltip" data-bs-placement="bottom" title="More Info" class="btn icon btn-primary mx-1" onclick="focusObject(`'+ item.id +'`);">' +
                '<span class="material-symbols-outlined">info</span>' +
                '</a>' +
                '</td>'+
                '</tr>';
        } else {
            row =
                '<tr>'+
                '<td>'+ counter +'</td>' +
                '<td class="fw-bold">'+ item.name +'</td>' +
                '<td>'+
                '<a data-bs-toggle="tooltip" data-bs-placement="bottom" title="More Info" class="btn icon btn-primary mx-1" onclick="focusObject(`'+ item.id +'`);">' +
                '<span class="material-symbols-outlined">info</span>' +
                '</a>' +
                '</td>'+
                '</tr>';
        }
        $('#table-data').append(row);
        objectMarker(item.id, item.lat, item.lng);
        counter++;
    }
}

// display steps of direction to selected route
function showSteps(directionResult) {
    $('#direction-row').show();
    $('#table-direction').empty();
    let myRoute = directionResult.routes[0].legs[0];
    for (let i = 0; i < myRoute.steps.length; i++) {
        let distance = myRoute.steps[i].distance.value;
        let instruction = myRoute.steps[i].instructions;
        let row =
            '<tr>' +
            '<td>'+ distance.toLocaleString("id-ID") +'</td>' +
            '<td>'+ instruction +'</td>' +
            '</tr>';
        $('#table-direction').append(row);
    }
}

// close nearby search section
function closeNearby() {
    $('#direction-row').hide();
    $('#check-nearby-col').hide();
    $('#result-nearby-col').hide();
    $('#list-rec-col').show();
    $('#list-rg-col').show();
    $('#list-ev-col').show();
    $('#list-up-col').show();
}

// open nearby search section
function openNearby(id, lat, lng) {
    $('#list-rg-col').hide();
    $('#list-ev-col').hide();
    $('#list-up-col').hide();
    $('#list-rec-col').hide();
    $('#check-nearby-col').show();

    currentLat = lat;
    currentLng = lng;
    let pos = new google.maps.LatLng(currentLat, currentLng);
    map.panTo(pos);

    document.getElementById('inputRadiusNearby').setAttribute('onchange', 'updateRadius("Nearby"); checkNearby("'+id+'")');
}

// Search Result Object Around
function checkNearby(id) {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click')

    objectMarker(id, currentLat, currentLng, false);

    $('#table-cp').empty();
    $('#table-wp').empty();
    $('#table-sp').empty();
    $('#table-cp').hide();
    $('#table-wp').hide();
    $('#table-sp').hide();

    let radiusValue = parseFloat(document.getElementById('inputRadiusNearby').value) * 100;
    const checkCP = document.getElementById('check-cp').checked;
    const checkWP = document.getElementById('check-wp').checked;
    const checkSP = document.getElementById('check-sp').checked;

    if (!checkCP && !checkWP && !checkSP) {
        document.getElementById('radiusValueNearby').innerHTML = "0 m";
        document.getElementById('inputRadiusNearby').value = 0;
        return Swal.fire('Please choose one object');
    }

    if (checkCP) {
        findNearby('cp', radiusValue);
        $('#table-cp').show();
    }
    if (checkWP) {
        findNearby('wp', radiusValue);
        $('#table-wp').show();
    }
    if (checkSP) {
        findNearby('sp', radiusValue);
        $('#table-sp').show();
    }
    drawRadius(new google.maps.LatLng(currentLat, currentLng), radiusValue);
    $('#result-nearby-col').show();
}

// Fetch object nearby by category
function findNearby(category, radius) {
    let pos = new google.maps.LatLng(currentLat, currentLng);
    if (category === 'cp') {
        $.ajax({
            url: baseUrl + '/api/culinaryPlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radius
            },
            dataType: 'json',
            success: function (response) {
                displayNearbyResult(category, response);
            }
        });
    } else if (category === 'wp') {
        $.ajax({
            url: baseUrl + '/api/worshipPlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radius
            },
            dataType: 'json',
            success: function (response) {
                displayNearbyResult(category, response);
            }
        });
    } else if (category === 'sp') {
        $.ajax({
            url: baseUrl + '/api/souvenirPlace/findByRadius',
            type: 'POST',
            data: {
                lat: currentLat,
                long: currentLng,
                radius: radius
            },
            dataType: 'json',
            success: function (response) {
                displayNearbyResult(category, response);
            }
        });
    }
}

// Add nearby object to corresponding table
function displayNearbyResult(category, response) {
    let data = response.data;
    let headerName;
    if (category === 'cp') {
        headerName = 'Culinary';
    } else if (category === 'wp') {
        headerName = 'Worship';
    } else if (category === 'sp') {
        headerName = 'Souvenir'
    }
    let table =
        '<thead><tr>' +
        '<th>'+ headerName +' Name</th>' +
        '<th>Action</th>' +
        '</tr></thead>' +
        '<tbody id="data-'+category+'">' +
        '</tbody>';
    $('#table-'+category).append(table);

    for (i in data) {
        let item = data[i];
        let row =
            '<tr>'+
            '<td class="fw-bold">'+ item.name +'</td>' +
            '<td>'+
            '<a title="Route" class="btn icon btn-primary mx-1" onclick="routeTo('+item.lat+', '+item.lng+', false)"><i class="fa-solid fa-road"></i></a>' +
            '<a title="Info" class="btn icon btn-primary mx-1" onclick="infoModal(`'+ item.id +'`)"><i class="fa-solid fa-info"></i></a>' +
            '<a title="Location" class="btn icon btn-primary mx-1" onclick="focusObject(`'+ item.id +'`);"><i class="fa-solid fa-location-dot"></i></a>' +
            '</td>'+
            '</tr>';
        $('#data-'+category).append(row);
        objectMarker(item.id, item.lat, item.lng);
    }
}

// Show modal for object
function infoModal(id) {
    let title, content;
    if (id.substring(0,1) === "C") {
        $.ajax({
            url: baseUrl + '/api/culinaryPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let item = response.data;
                let open = item.open.substring(0, item.open.length - 3);
                let close = item.close.substring(0, item.close.length - 3);

                title = '<h3>'+item.name+'</h3>';
                content =
                    '<div class="text-start">'+
                    '<p><span class="fw-bold">Address</span>: '+ item.address +'</p>'+
                    '<p><span class="fw-bold">Open</span>: '+ open +' - '+ close+' WIB</p>'+
                    '<p><span class="fw-bold">Contact Person:</span> '+ item.contact_person+'</p>'+
                    '</div>'+
                    '<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">' +
                    '<ol class="carousel-indicators">' +
                    '<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"></li>' +
                    '</ol><div class="carousel-inner">' +
                    '<div class="carousel-item active">' +
                    '<img src="/media/photos/'+item.gallery[0]+'" alt="'+ item.name +'" class="w-50" alt="'+item.name+'">' +
                    '</div></div>' +
                    '<a style="color: #000" class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">\n' +
                    '<i class="fa-solid fa-angle-left" aria-hidden="true"></i>' +
                    '<span class="visually-hidden">Previous</span>' +
                    ' </a>' +
                    '<a style="color: #000" class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">' +
                    '<i class="fa-solid fa-angle-right" aria-hidden="true"></i>' +
                    '<span class="visually-hidden">Next</span>' +
                    '</a>' +
                    '</div>';

                Swal.fire({
                    title: title,
                    html: content,
                    width: '50%',
                    position: 'top'
                });
            }
        });
    } else if (id.substring(0,1) === "W") {
        $.ajax({
            url: baseUrl + '/api/worshipPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let item = response.data;

                title = '<h3>'+item.name+'</h3>';
                content =
                    '<div class="text-start">'+
                    '<p><span class="fw-bold">Address</span>: '+ item.address +'</p>'+
                    '<p><span class="fw-bold">Park Area :</span> '+ item.parking_area+' m<sup>2</sup></p>'+
                    '<p><span class="fw-bold">Building Area</span>: '+ item.building_area+' m<sup>2</sup></p>'+
                    '<p><span class="fw-bold">Capacity</span>: '+ item.capacity+'</p>'+
                    '</div>' +
                    '<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">' +
                    '<ol class="carousel-indicators">' +
                    '<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"></li>' +
                    '</ol><div class="carousel-inner">' +
                    '<div class="carousel-item active">' +
                    '<img src="/media/photos/'+item.gallery[0]+'" alt="'+ item.name +'" class="w-50" alt="'+item.name+'">' +
                    '</div></div>' +
                    '<a style="color: #000" class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">\n' +
                    '<i class="fa-solid fa-angle-left" aria-hidden="true"></i>' +
                    '<span class="visually-hidden">Previous</span>' +
                    ' </a>' +
                    '<a style="color: #000" class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">' +
                    '<i class="fa-solid fa-angle-right" aria-hidden="true"></i>' +
                    '<span class="visually-hidden">Next</span>' +
                    '</a>' +
                    '</div>';

                Swal.fire({
                    title: title,
                    html: content,
                    width: '50%',
                    position: 'top'
                });
            }
        });
    } else if (id.substring(0,1) === "S") {
        $.ajax({
            url: baseUrl + '/api/souvenirPlace/' + id,
            dataType: 'json',
            success: function (response) {
                let item = response.data;
                console.log(item)
                let open = item.open.substring(0, item.open.length - 3);
                let close = item.close.substring(0, item.close.length - 3);

                title = '<h3>'+item.name+'</h3>';
                content =
                    '<div class="text-start">'+
                    '<p><span class="fw-bold">Address</span>: '+ item.address +'</p>'+
                    '<p><span class="fw-bold">Contact Person :</span> '+ item.contact_person+'</p>'+
                    '<p><span class="fw-bold">Open</span>: '+ open +' - '+ close+' WIB</p>'+
                    '</div>' +
                    '<div id="carouselExampleControls" class="carousel slide" data-ride="carousel">' +
                    '<ol class="carousel-indicators">' +
                    '<li data-bs-target="#carouselExampleCaptions" data-bs-slide-to="0" class="active"></li>' +
                    '</ol><div class="carousel-inner">' +
                    '<div class="carousel-item active">' +
                    '<img src="/media/photos/'+item.gallery[0]+'" alt="'+ item.name +'" class="w-50" alt="'+item.name+'">' +
                    '</div></div>' +
                    '<a style="color: #000" class="carousel-control-prev" href="#carouselExampleControls" role="button" data-bs-slide="prev">\n' +
                    '<i class="fa-solid fa-angle-left" aria-hidden="true"></i>' +
                    '<span class="visually-hidden">Previous</span>' +
                    ' </a>' +
                    '<a style="color: #000" class="carousel-control-next" href="#carouselExampleControls" role="button" data-bs-slide="next">' +
                    '<i class="fa-solid fa-angle-right" aria-hidden="true"></i>' +
                    '<span class="visually-hidden">Next</span>' +
                    '</a>' +
                    '</div>';

                Swal.fire({
                    title: title,
                    html: content,
                    width: '50%',
                    position: 'top'
                });
            }
        });
    }
}

// Find object by name
function findByName(category) {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    closeNearby();

    let name;
    if (category === 'RG') {
        name = document.getElementById('nameRG').value;
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByName',
            type: 'POST',
            data: {
                name: name,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (category === 'EV') {
        name = document.getElementById('nameEV').value;
        $.ajax({
            url: baseUrl + '/api/event/findByName',
            type: 'POST',
            data: {
                name: name,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (category === 'UP') {
        name = document.getElementById('nameUP').value;
        $.ajax({
            url: baseUrl + '/api/uniquePlace/findByName',
            type: 'POST',
            data: {
                name: name,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    }
}

// Get list of Rumah Gadang facilities
function getFacility() {
    let facility;
    $('#facilitySelect').empty()
    $.ajax({
        url: baseUrl + '/api/facility',
        dataType: 'json',
        success: function (response) {
            let data = response.data;
            for (i in data) {
                let item = data[i];
                facility =
                    '<option value="'+ item.id +'">'+ item.facility +'</option>';
                $('#facilitySelect').append(facility);
            }
        }
    });
}

// Find object by Facility
function findByFacility() {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    closeNearby();

    let facility = document.getElementById('facilitySelect').value;
    $.ajax({
        url: baseUrl + '/api/rumahGadang/findByFacility',
        type: 'POST',
        data: {
            facility: facility,
        },
        dataType: 'json',
        success: function (response) {
            displayFoundObject(response);
            boundToObject();
        }
    });
}

// Set star by user input
function setStar(star) {
    switch (star) {
        case 'star-1' :
            $("#star-1").addClass('star-checked');
            $("#star-2,#star-3,#star-4,#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '1';
            break;
        case 'star-2' :
            $("#star-1,#star-2").addClass('star-checked');
            $("#star-3,#star-4,#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '2';
            break;
        case 'star-3' :
            $("#star-1,#star-2,#star-3").addClass('star-checked');
            $("#star-4,#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '3';
            break;
        case 'star-4' :
            $("#star-1,#star-2,#star-3,#star-4").addClass('star-checked');
            $("#star-5").removeClass('star-checked');
            document.getElementById('star-rating').value = '4';
            break;
        case 'star-5' :
            $("#star-1,#star-2,#star-3,#star-4,#star-5").addClass('star-checked');
            document.getElementById('star-rating').value = '5';
            break;
    }
}

// Find object by Rating
function findByRating(category) {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    closeNearby();

    let rating = document.getElementById('star-rating').value;
    if (category === 'RG') {
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByRating',
            type: 'POST',
            data: {
                rating: rating,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (category === 'EV') {
        $.ajax({
            url: baseUrl + '/api/event/findByRating',
            type: 'POST',
            data: {
                rating: rating,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (category === 'UP') {
        $.ajax({
            url: baseUrl + '/api/uniquePlace/findByRating',
            type: 'POST',
            data: {
                rating: rating,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    }
}

// Find object by Category
function findByCategory(object) {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    closeNearby();

    if (object === 'RG') {
        let category = document.getElementById('categoryRGSelect').value;
        $.ajax({
            url: baseUrl + '/api/rumahGadang/findByCategory',
            type: 'POST',
            data: {
                category: category,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (object === 'EV') {
        let category = document.getElementById('categoryEVSelect').value;
        $.ajax({
            url: baseUrl + '/api/event/findByCategory',
            type: 'POST',
            data: {
                category: category,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    } else if (object === 'UP') {
        let category = document.getElementById('categoryUPSelect').value;
        $.ajax({
            url: baseUrl + '/api/uniquePlace/findByCategory',
            type: 'POST',
            data: {
                category: category,
            },
            dataType: 'json',
            success: function (response) {
                displayFoundObject(response);
                boundToObject();
            }
        });
    }
}

// Get list of Event category
function getCategory() {
    let category;
    $('#categoryEVSelect').empty()
    $.ajax({
        url: baseUrl + '/api/event/category',
        dataType: 'json',
        success: function (response) {
            let data = response.data;
            for (i in data) {
                let item = data[i];
                category =
                    '<option value="'+ item.id +'">'+ item.category +'</option>';
                $('#categoryEVSelect').append(category);
            }
        }
    });
}

// // Find object by Date
function findByDate() {
    clearRadius();
    clearRoute();
    clearMarker();
    clearUser();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');
    closeNearby();

    let eventDate = document.getElementById('eventDate').value;
    $.ajax({
        url: baseUrl + '/api/event/findByDate',
        type: 'POST',
        data: {
            date: eventDate,
        },
        dataType: 'json',
        success: function (response) {
            displayFoundObject(response);
            boundToObject();
        }
    });
}

// Create compass
function setCompass() {
    const compass = document.createElement("div");
    compass.setAttribute("id", "compass");
    const compassDiv = document.createElement("div");
    compass.appendChild(compassDiv);
    const compassImg = document.createElement("img");
    compassImg.src = baseUrl + '/media/icon/compass.png';
    compassDiv.appendChild(compassImg);

    map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(compass);
}

// Create legend
function getLegend() {
    const icons = {
        rg :{
            name: 'Rumah Gadang',
            icon: baseUrl + '/media/icon/marker_rg.png',
        },
        ev :{
            name: 'Event',
            icon: baseUrl + '/media/icon/marker_ev.png',
        },
        up :{
            name: 'Unique Place',
            icon: 'https://maps.gstatic.com/mapfiles/ms2/micons/red-dot.png',
        },
        cp :{
            name: 'Culinary Place',
            icon: baseUrl + '/media/icon/marker_cp.png',
        },
        wp :{
            name: 'Worship Place',
            icon: baseUrl + '/media/icon/marker_wp.png',
        },
        sp :{
            name: 'Souvenir Place',
            icon: baseUrl + '/media/icon/marker_sp.png',
        },
    }

    const title = '<p class="fw-bold fs-6">Legend</p>';
    $('#legend').append(title);

    for (key in icons) {
        const type = icons[key];
        const name = type.name;
        const icon = type.icon;
        const div = '<div><img src="' + icon + '"> ' + name +'</div>';

        $('#legend').append(div);
    }
    map.controls[google.maps.ControlPosition.RIGHT_BOTTOM].push(legend);
}

// toggle legend element
function viewLegend() {
    if ($('#legend').is(':hidden')) {
        $('#legend').show();
    } else {
        $('#legend').hide();
    }
}

// list object for new visit history
function getObjectByCategory(){
    const category = document.getElementById('category').value;
    $('#object').empty();
    if (category === 'None') {
        object =
            '<option value="None">Select Category First</option>';
        $('#object').append(object);
        return Swal.fire({
            icon: 'warning',
            title: 'Please Choose a Object Category!'
        });
    }
    if (category === '1') {
        $.ajax({
            url: baseUrl + '/api/rumahGadang',
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                for (i in data) {
                    let item = data[i];
                    object =
                        '<option value="'+ item.id +'">'+ item.name +'</option>';
                    $('#object').append(object);
                }
            }
        });
    } else if (category === '2') {
        $.ajax({
            url: baseUrl + '/api/event',
            dataType: 'json',
            success: function (response) {
                let data = response.data;
                for (i in data) {
                    let item = data[i];
                    object =
                        '<option value="'+ item.id +'">'+ item.name +'</option>';
                    $('#object').append(object);
                }
            }
        });
    }
}

// Validate if star rating picked yet
function checkStar(event) {
    const star = document.getElementById('star-rating').value;
    if (star == '0') {
        event.preventDefault();
        Swal.fire('Please put rating star');
    }
}

// Check if Category and Object is chose correctly
function checkForm(event) {
    const category = document.getElementById('category').value;
    const object = document.getElementById('object').value;
    if (category === 'None' || object === 'None') {
        event.preventDefault();
        Swal.fire('Please select the correct Category and Object');
    }
}

// Update preview of uploaded photo profile
function showPreview(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function (e) {
            $('#avatar-preview').attr('src', e.target.result).width(300).height(300);
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Get list of Recommendation
function getRecommendation(id, recom) {
    let recommendation;
    $('#recommendationSelect' + id).empty()
    $.ajax({
        url: baseUrl + '/api/recommendationList',
        dataType: 'json',
        success: function (response) {
            let data = response.data;
            for (i in data) {
                let item = data[i];
                if (item.id == recom) {
                    recommendation =
                        '<option value="'+ item.id +'" selected>'+ item.name +'</option>';
                } else {
                    recommendation =
                        '<option value="'+ item.id +'">'+ item.name +'</option>';
                }
                $('#recommendationSelect' + id).append(recommendation);
            }
        }
    });
}

// Update option onclick function for updating Recommendation
function changeRecom(status = null) {
    if (status === 'edit') {
        $('#recomBtnEdit').hide();
        $('#recomBtnExit').show();
        console.log('entering edit mode');
        $('.recomSelect').on('change', updateRecom);
    } else {
        $('#recomBtnEdit').show();
        $('#recomBtnExit').hide();
        console.log('exiting edit mode');
        $('.recomSelect').off('change', updateRecom);
    }
}

// Update recommendation based on input User
function updateRecom() {
    let recom = $(this).find('option:selected').val();
    let id = $(this).attr('id');
    $.ajax({
        url: baseUrl + '/api/recommendation',
        type: 'POST',
        data: {
            id: id,
            recom: recom,
        },
        dataType: 'json',
        success: function (response) {
            if (response.status === 201) {
                console.log('Success update recommendation @' + id + ':' + recom);
                Swal.fire('Success updating Rumah Gadang ID @' + id)
            }
        }
    });
}

// Set map to coordinate put by user
function findCoords(object) {
    clearMarker();
    google.maps.event.clearListeners(map, 'click');

    const lat = Number(document.getElementById('latitude').value);
    const lng = Number(document.getElementById('longitude').value);

    if (lat === 0 || lng === 0 || isNaN(lat) || isNaN(lng)) {
        return Swal.fire('Please input Lat and Long');
    }

    let pos = new google.maps.LatLng(lat, lng);
    map.panTo(pos);
}

// Unselect shape on drawing map
function clearSelection() {
    if (selectedShape) {
        selectedShape.setEditable(false);
        selectedShape = null;
    }
}

// Make selected shape editable on maps
function setSelection(shape) {
    clearSelection();
    selectedShape = shape;
    shape.setEditable(true);
}

// Remove selected shape on maps
function deleteSelectedShape() {
    if (selectedShape) {
        document.getElementById('latitude').value = '';
        document.getElementById('longitude').value = '';
        document.getElementById('geo-json').value = '';
        document.getElementById('lat').value = '';
        document.getElementById('lng').value = '';
        clearMarker();
        selectedShape.setMap(null);
        // To show:
        drawingManager.setOptions({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true
        });
    }
}

// Initialize drawing manager on maps
function initDrawingManager(edit = false) {
    const drawingManagerOpts = {
        drawingMode: google.maps.drawing.OverlayType.POLYGON,
        drawingControl: true,
        drawingControlOptions: {
            position: google.maps.ControlPosition.TOP_CENTER,
            drawingModes: [
                google.maps.drawing.OverlayType.POLYGON,
            ]
        },
        polygonOptions: {
            fillColor: 'blue',
            strokeColor: 'blue',
            editable: true,
        },
        map: map
    };
    drawingManager.setOptions(drawingManagerOpts);
    let newShape;

    if (!edit) {
        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            drawingManager.setOptions({
                drawingControl: false,
                drawingMode: null,
            });
            newShape = event.overlay;
            newShape.type = event.type;
            setSelection(newShape);
            saveSelection(newShape);

            google.maps.event.addListener(newShape, 'click', function() {
                setSelection(newShape);
            });
            google.maps.event.addListener(newShape.getPath(), 'insert_at', () => {
                saveSelection(newShape);
            });
            google.maps.event.addListener(newShape.getPath(), 'remove_at', () => {
                saveSelection(newShape);
            });
            google.maps.event.addListener(newShape.getPath(), 'set_at', () => {
                saveSelection(newShape);
            });
        });
    } else {
        drawingManager.setOptions({
            drawingControl: false,
            drawingMode: null,
        });

        newShape = drawGeom();
        newShape.type = 'polygon';
        setSelection(newShape);

        const paths = newShape.getPath().getArray();
        let bounds = new google.maps.LatLngBounds();
        for (let i = 0; i < paths.length; i++) {
            bounds.extend(paths[i])
        }
        let pos = bounds.getCenter();
        map.panTo(pos);

        clearMarker();
        let marker = new google.maps.Marker();
        markerOption = {
            position: pos,
            animation: google.maps.Animation.DROP,
            map: map,
        }
        marker.setOptions(markerOption);
        markerArray['newRG'] = marker;

        google.maps.event.addListener(newShape, 'click', function() {
            setSelection(newShape);
        });
        google.maps.event.addListener(newShape.getPath(), 'insert_at', () => {
            saveSelection(newShape);
        });
        google.maps.event.addListener(newShape.getPath(), 'remove_at', () => {
            saveSelection(newShape);
        });
        google.maps.event.addListener(newShape.getPath(), 'set_at', () => {
            saveSelection(newShape);
        });
    }

    google.maps.event.addListener(map, 'click', clearSelection);
    google.maps.event.addDomListener(document.getElementById('clear-drawing'), 'click', deleteSelectedShape);
}

// Get geoJSON of selected shape on map
function saveSelection(shape) {

    const paths = shape.getPath().getArray();
    let bounds = new google.maps.LatLngBounds();
    for (let i = 0; i < paths.length; i++) {
        bounds.extend(paths[i])
    }
    let pos = bounds.getCenter();
    map.panTo(pos);

    clearMarker();
    let marker = new google.maps.Marker();
    markerOption = {
        position: pos,
        animation: google.maps.Animation.DROP,
        map: map,
    }
    marker.setOptions(markerOption);
    markerArray['newRG'] = marker;

    document.getElementById('latitude').value = pos.lat().toFixed(8);
    document.getElementById('longitude').value = pos.lng().toFixed(8);
    document.getElementById('lat').value = pos.lat().toFixed(8);
    document.getElementById('lng').value = pos.lng().toFixed(8);

    const dataLayer = new google.maps.Data();
    dataLayer.add(new google.maps.Data.Feature({
        geometry: new google.maps.Data.Polygon([shape.getPath().getArray()])
    }));
    dataLayer.toGeoJson(function (object) {
        document.getElementById('geo-json').value = JSON.stringify(object.features[0].geometry);
    });

}

// Get list of users
function getListUsers(owner) {
    let users;
    $('#ownerSelect').empty()
    $.ajax({
        url: baseUrl + '/api/owner',
        dataType: 'json',
        success: function (response) {
            let data = response.data;
            for (i in data) {
                let item = data[i];
                if (item.id == owner) {
                    users =
                        '<option value="'+ item.id +'" selected>'+ item.first_name +' ' + item.last_name +'</option>';
                } else {
                    users =
                        '<option value="'+ item.id +'">'+ item.first_name +' ' + item.last_name +'</option>';
                }
                $('#ownerSelect').append(users);
            }
        }
    });
}

// Draw current GeoJSON on drawing manager
function drawGeom() {
    const geoJSON = $('#geo-json').val();
    if (geoJSON !== '') {
        const geoObj = JSON.parse(geoJSON);
        const coords = geoObj.coordinates[0];
        let polygonCoords = []
        for (i in coords) {
            polygonCoords.push(
                {lat: coords[i][1], lng: coords[i][0]}
            );
        }
        const polygon = new google.maps.Polygon({
            paths: polygonCoords,
            fillColor: 'blue',
            strokeColor: 'blue',
            editable: true,
        });
        polygon.setMap(map);
        return polygon;
    }
}

// Delete selected object
function deleteObject(id = null, name = null, user = false) {
    
    if (id === null) {
        return Swal.fire('ID cannot be null');
    }

    let content, apiUri;
    if (id.substring(0,1) === 'R') {
        content = 'Rumah Gadang';
        apiUri = 'rumahGadang/';
    } else if (id.substring(0,1) === 'E') {
        content = 'Event';
        apiUri = 'event/'
    } else if (id.substring(0,1) === 'U') {
        content = 'Unique Place';
        apiUri = 'uniquePlace/'
    } else if (id.substring(0,1) === 'P') {
        content = 'Tourism Package';
        apiUri = 'package/'
    } else if (id.substring(0,2) === 'SP') {
        console.log(id)
        content = 'Service';
        apiUri = 'service/'
    } else if (user === true) {
        content = 'User';
        apiUri = 'user/'
    } else  {
        content = 'Facility';
        apiUri = 'facility/'
    }
    
    Swal.fire({
        title: 'Delete ' + content + '?',
        text: 'You are about to remove ' + name,
        icon: 'warning',
        showCancelButton: true,
        denyButtonText: 'Delete',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#343a40',
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: baseUrl + '/api/' + apiUri + id,
                type: 'DELETE',
                dataType: 'json',
                success: function (response) {
                    console.log(response)
                    if (response.status === 200) {
                        Swal.fire('Deleted!', 'Successfully remove ' + name, 'success').then((result) => {
                            if(result.isConfirmed) {
                                document.location.reload();
                            }
                        });

                    } else {
                        Swal.fire('Failed', 'Delete ' + name + ' failed!', 'warning');
                    }
                }
            });
        }
    });
}

/// Android API ///

// Get user's current position
function userPositionAPI(lat = null, lng = null) {

    clearRadius();
    clearRoute();

    infoWindow.close();
    let pos = new google.maps.LatLng(lat, lng);

    clearUser();
    markerOption = {
        position: pos,
        map: map,
    };
    userMarker.setOptions(markerOption);

    setUserLoc(pos.lat().toFixed(8), pos.lng().toFixed(8))

}

// Pan map to user position
function panToUser() {
    if (userLat == 0 && userLng == 0) {
        return Swal.fire('Determine your position first!');
    }
    let pos = new google.maps.LatLng(userLat, userLng);
    map.panTo(pos);
}

// Find RG on mobile
function findRG(name = null){
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/rumahGadang/findByName',
        type: 'POST',
        data: {
            name: name,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

// Find RG by Rating on Mobile
function findByRatingRG(rating) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/rumahGadang/findByRating',
        type: 'POST',
        data: {
            rating: rating,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

// Find object by Facility on Mobile
function findByFacilityRG(facility) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/rumahGadang/findByFacility',
        type: 'POST',
        data: {
            facility: facility,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

// Find RG by Category on Mobile
function findByCategoryRG(category) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/rumahGadang/findByCategory',
        type: 'POST',
        data: {
            category: category,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

// Find EV on mobile
function findEV(name = null){
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/event/findByName',
        type: 'POST',
        data: {
            name: name,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

// Find EV by Rating on Mobile
function findByRatingEV(rating) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/event/findByRating',
        type: 'POST',
        data: {
            rating: rating,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}


// Find EV by Category on Mobile
function findByCategoryEV(category) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    currentUrl = 'mobile'
    $.ajax({
        url: baseUrl + '/api/event/findByCategory',
        type: 'POST',
        data: {
            category: category,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

// // Find EV by Date
function findByDateEV(eventDate) {
    clearRadius();
    clearRoute();
    clearMarker();
    destinationMarker.setMap(null);
    google.maps.event.clearListeners(map, 'click');

    $.ajax({
        url: baseUrl + '/api/event/findByDate',
        type: 'POST',
        data: {
            date: eventDate,
        },
        dataType: 'json',
        success: function (response) {
            let data = response.data
            for (i in data) {
                let item = data[i]
                currentUrl = currentUrl + item.id
                objectMarker(item.id, item.lat, item.lng);
            }
            boundToObject();
        }
    });
}

 let regionData =  {"type":"Polygon","coordinates":[[[99.17835126627628,0.21076553534998096],[99.19483075846378,0.2876690989551999],[99.25800214518253,0.3343603042917253],[99.31293378580753,0.3371068391701154],[99.33765302408878,0.37006519548431277],[99.36511884440128,0.42774201782218885],[99.38434491862003,0.46619299551329973],[99.42554364908878,0.48816488960694554],[99.46399579752628,0.5183761261063307],[99.51343427408878,0.5403478444227927],[99.56561933268253,0.5101367119103833],[99.61780439127628,0.5321084591927038],[99.64527021158878,0.5540801282231124],[99.65900312174503,0.5128831844896459],[99.68097577799503,0.4771789513319652],[99.70569501627628,0.4634465038647417],[99.73316083658878,0.46070001115127035],[99.77435956705753,0.48816488960694554],[99.80457196940128,0.4826719226876505],[99.83203778971378,0.4634465038647417],[99.88147626627628,0.4634465038647417],[99.91718183268253,0.4799254375612143],[99.95014081705753,0.5046437632415437],[99.96936689127628,0.5513336739841367],[99.94190107096378,0.5897839151746955],[99.91443525065128,0.6282338907469042],[99.91992841471378,0.6721763725151011],[99.89795575846378,0.6941474667440013],[99.85675702799503,0.6804155446080657],[99.82929120768253,0.7380893457396961],[99.84851728190128,0.762806463541919],[99.84851728190128,0.8122402686458721],[99.84027753580753,0.8671660079944127],[99.86499677408878,0.9056135543506701],[99.91168866862003,0.8836435766881487],[99.94464765299503,0.8836435766881487],[99.97211347330753,0.8534346459009013],[100.00781903971378,0.836956946749794],[100.04166163819818,0.822294089926112],[100.08286036866693,0.7975773215905024],[100.12405909913568,0.786592043299371],[100.15976466554193,0.7728604048181134],[100.19821681397943,0.7453969954609254],[100.22842921632318,0.7453969954609254],[100.23392238038568,0.6959624312599788],[100.20096339601068,0.652020160308208],[100.17899073976068,0.5998382175068337],[100.18723048585443,0.555895141142692],[100.23392238038568,0.5531486877444006],[100.21744288819818,0.5146982095814882],[100.23666896241693,0.4872334393904186],[100.25040187257318,0.4432895768367459],[100.27237452882318,0.4075849953134454],[100.33279933351068,0.39385242105511425],[100.37399806397943,0.3828663453201355],[100.41245021241693,0.4158245286800313],[100.45639552491693,0.4075849953134454],[100.49210109132318,0.38561286558933466],[100.51132716554193,0.35265456568198195],[100.54153956788568,0.31694960944056294],[100.56900538819818,0.28673762669678016],[100.59097804444818,0.25927211843374814],[100.61295070069818,0.2400462268248845],[100.64316310304193,0.2070874938113757],[100.67062892335443,0.19060810092590866],[100.69534816163568,0.2208203081872855],[100.72830714601068,0.2400462268248845],[100.76126613038568,0.2318065505921842],[100.78873195069818,0.2537790094297502],[100.82718409913568,0.2427927844743159],[100.84091700929193,0.21258062103352732],[100.84641017335443,0.16863555278603873],[100.82718409913568,0.135676685084474],[100.79147853272943,0.1137040810014207],[100.77774562257318,0.06701224582538941],[100.79971827882318,0.04778618134461322],[100.82718409913568,0.06426566559675506],[100.84915675538568,0.04778618134461322],[100.84091700929193,0.009334038405846123],[100.82718409913568,-0.020878363434576323],[100.80521144288568,-0.04010443484060323],[100.76675929444818,-0.015385199649238823],[100.75302638429193,-0.045597597364723574],[100.76401271241693,-0.11426207722174396],[100.76675929444818,-0.17194011754620345],[100.76675929444818,-0.22137830135717695],[100.79422511476068,-0.24060421924080638],[100.82169093507318,-0.2790559715686685],[100.83542384522943,-0.3147610579894528],[100.86288966554193,-0.3394798932004689],[100.89584864991693,-0.3367333589910131],[100.94803370851068,-0.32025413770579647],[100.98099269288568,-0.32025413770579647],[101.00571193116693,-0.3257472144784986],[101.03592433351068,-0.3532125524202448],[101.03317775147943,-0.3999034372697171],[101.03867091554193,-0.43835455616692187],[101.04965724366693,-0.46307302943637485],[101.06339015382318,-0.49877733746030517],[101.09085597413568,-0.5070167661564395],[101.12106837647943,-0.512509712805856],[101.17600001710443,-0.5482137489496111],[101.20071925538568,-0.5921568843379268],[101.23917140382318,-0.616874746577318],[101.28037013429193,-0.6306068429500821],[101.31882228272943,-0.6388460834364097],[101.35178126710443,-0.671802911566065],[101.39847316163568,-0.7157450014158309],[101.44241847413568,-0.7651793479707208],[101.48636378663568,-0.7953889483468253],[101.55502833741693,-0.8283446235410695],[101.60995997804193,-0.831090917586625],[101.64291896241693,-0.8283446235410695],[101.68686427491693,-0.861300024670759],[101.71982325929193,-0.8887626417265604],[101.69235743897943,-0.9381948336040457],[101.76376857179193,-0.9738954288489048],[101.79672755616693,-0.9684030538463954],[101.84616603272943,-0.9766416129970217],[101.82215751043952,-0.9936071037258166],[101.8262773834864,-1.0354859124845315],[101.82009757391609,-1.0622606000479526],[101.8262773834864,-1.0945272224227236],[101.83520377508796,-1.1151227579577376],[101.85305655829109,-1.0986463409303457],[101.86678946844734,-1.1144362424134844],[101.87983573309577,-1.0890350556153032],[101.88532889715827,-1.1329721056197601],[101.85516462216826,-1.1695094839289095],[101.81602582822295,-1.2216831795159144],[101.79336652646514,-1.220996690004298],[101.7748270977542,-1.2402183297030627],[101.73706159482451,-1.2360994185891825],[101.72264203916045,-1.2429642668856247],[101.70897550174986,-1.2751624568961213],[101.69592923710142,-1.2902648725411674],[101.70828885624205,-1.3266475945752],[101.72408170292174,-1.3561652596277811],[101.70828885624205,-1.3801910006562708],[101.69944984663208,-1.3910797097494354],[101.69738991010864,-1.4010331142443946],[101.70768959272583,-1.4154483146659425],[101.71833259809692,-1.4181940569947735],[101.72382576215942,-1.4336387967151274],[101.71489937055786,-1.4449648730188995],[101.7004798148938,-1.4583501630772677],[101.69224006880005,-1.4724217924156988],[101.68123187628548,-1.4886742632157413],[101.67402209845345,-1.4975976238860143],[101.66990222540657,-1.5089233754626594],[101.6606325110511,-1.5154442360106122],[101.65067615118782,-1.5181898556355393],[101.64655627814095,-1.5253970905438263],[101.63351001349251,-1.5353498990372136],[101.62973346319954,-1.5446162652947109],[101.61840381232064,-1.5497642291029243],[101.60741748419564,-1.5586873367021608],[101.58711520881234,-1.5751606666105096],[101.57338229865609,-1.576876631009331],[101.56411258430062,-1.5854564317514939],[101.56170932502327,-1.5930066269950147],[101.54969302863655,-1.593349817031447],[101.54419986457405,-1.6019295493269408],[101.53168870062409,-1.621148018997795],[101.5213890180069,-1.6386503951967992],[101.50971604437409,-1.6588980512456108],[101.50628281683503,-1.674684215714933],[101.50113297552643,-1.6798318506061225],[101.48019028753815,-1.672625157970081],[101.47504044622956,-1.6781159738140257],[101.46165085882721,-1.6815477258912088],[101.44757462591706,-1.686008994568584],[101.4393348798233,-1.6980200516487771],[101.42972184271393,-1.6870385166584645],[101.43040848822174,-1.680175025783754],[101.41358567328034,-1.6719388049081658],[101.40328599066315,-1.6781159738140257],[101.400196085878,-1.687724864415698],[101.38749314398346,-1.6890975592033315],[101.38097001165924,-1.6849794719342053],[101.37582017035065,-1.6904702530211502],[101.36552048773346,-1.692872464866378],[101.35281754583893,-1.6925292919277806],[101.34320450872956,-1.6897839062335154],[101.33633805365143,-1.6870385166584645],[101.33290482611237,-1.692872464866378],[101.3294715985733,-1.6973337075356707],[101.31917191595612,-1.6973337075356707],[101.30990220160065,-1.7007654256629132],[101.2851829633194,-1.6969905353877592],[101.2741966351944,-1.6942451560131924],[101.2635536298233,-1.6963041909092451],[101.25325394720612,-1.7045403085533537],[101.24226761908112,-1.7124332215708713],[101.23196793646393,-1.7247872809785256],[101.22578812689362,-1.7186102612653105],[101.21651841253815,-1.7258167823164063],[101.20518876165924,-1.718953429552016],[101.1969490155655,-1.7117468825967652],[101.19660569281159,-1.6980200516487771],[101.19420243353424,-1.6818909007672473],[101.18836594671784,-1.6764000955164453],[101.18184281439362,-1.6976768796226809],[101.1756630048233,-1.7045403085533537],[101.1698265180069,-1.7114037130175588],[101.15746689886628,-1.7021381112056284],[101.14957047552643,-1.6914997727476115],[101.136524210878,-1.6849794719342053],[101.13240433783112,-1.68566582041761],[101.1317176923233,-1.6976768796226809],[101.13119782970203,-1.7273811407041968],[101.13119782970203,-1.741794088849101],[101.1298245386864,-1.7634133040586248],[101.14184083507313,-1.785032268125317],[101.14355744884266,-1.7973858479363167],[101.15420045421375,-1.8052783690928442],[101.15832032726063,-1.8351323770400259],[101.17582978770984,-1.8574367707868429],[101.17479981944813,-1.8694467125440504],[101.1627835230614,-1.8838585341450484],[101.1627835230614,-1.8951820246606763],[101.14973725841297,-1.905476042701448],[101.14184083507313,-1.9023878437347543],[101.14527406261219,-1.9185150438479295],[101.14321412608875,-1.9253776361495707],[101.1514538721825,-1.9329264557629005],[101.16347016856922,-1.9428771212204765],[101.17479981944813,-1.9517983576615452],[101.18990602062,-1.972385645345443],[101.20432557628406,-1.9795911359864373],[101.20947541759266,-2.012187010173168],[101.23453797862781,-2.056104629498284],[101.25925721690906,-2.0917868065222427],[101.27299012706531,-2.1494255258340673],[101.28122987315906,-2.218040206590779],[101.29770936534656,-2.297629242790935],[101.24552430675281,-2.319584064919846],[101.20432557628406,-2.366236926489696],[101.14664735362781,-2.388190673042275],[101.08622254894031,-2.4320971104668327],[101.02030458019031,-2.489722136866309],[100.64951600597156,-3.542933540567191],[99.70469178722156,-3.323601472749874],[98.71592225597156,-1.699241260981684],[98.01279725597156,-0.6228179906803741],[98.05674256847156,0.12423995854831663],[98.38633241222156,0.3219922672632508],[99.11143006847156,0.34396455232365475],[99.1781922989041,0.2156081482000307],[99.17835126627628,0.21076553534998096]]]};