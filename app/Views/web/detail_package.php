<?= $this->extend('web/layouts/main'); ?>

<?= $this->section('content') ?>
<!-- Modal reservation -->
<div class="modal fade text-left" id="reservationModal" tabindex="-1" aria-labelledby="myModalLabel1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitle"></h5>
                <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="modalBody">
            </div>
            <div class="modal-footer" id="modalFooter">
            </div>
        </div>
    </div>
</div>
<section class="section">
    <div class="row">
        <script>
            currentUrl = '<?= current_url(); ?>';
        </script>

        <!-- Object Detail Information -->
        <div class="col-md-6 col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title text-center">Tourism Package Information</h4>
                    <a class="btn btn-primary" onclick="showReservationModal()" data-bs-toggle="modal" data-bs-target="#reservationModal"> Reservation <i class="fa fa-ticket"></i> </a>
                    <div class="text-center">
                        <?php for ($i = 0; $i < (int)esc($data['avg_rating']); $i++) { ?>
                            <span class="material-symbols-outlined rating-color">star</span>
                        <?php } ?>
                        <?php for ($i = 0; $i < (5 - (int)esc($data['avg_rating'])); $i++) { ?>
                            <span class="material-symbols-outlined">star</span>
                        <?php } ?>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col table-responsive">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <td class="fw-bold">Name</td>
                                        <td><?= esc($data['name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Price</td>
                                        <td><?= 'Rp ' . number_format(esc($data['price']), 0, ',', '.'); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Capacity</td>
                                        <td><?= esc($data['capacity']) ?> people</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold">Contact Person</td>
                                        <td><?= esc($data['contact_person']); ?></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col">
                            <p class="fw-bold">Description</p>
                            <p><?= esc($data['description']); ?></p>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col">
                            <p class="fw-bold">Service</p>
                            <?php $i = 1; ?>
                            <?php foreach ($data['services'] as $service) : ?>
                                <p class="px-1"><?= esc($i) . '. ' . esc($service); ?></p>
                                <?php $i++; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col border p-2">
                            <p class="fw-bold">Detail Packages </p>
                            <div class="list-group list-group-horizontal-sm mb-4 text-center" role="tablist">
                                <?php $dayNumber = 1; ?>
                                <?php foreach ($data['package_day'] as $day) : ?>
                                    <a onclick="getObjectsByPackageDayId('<?= $day['day'] ?>')" class="list-group-item list-group-item-action <?= $dayNumber == 1 ? "active" : "" ?>" id="list-<?= $dayNumber; ?>-list" data-bs-toggle="list" href="#list-<?= $dayNumber; ?>" role="tab" aria-selected="<?= $dayNumber == 1 ? "true" : "false" ?>"> Day <?= $dayNumber; ?></a>
                                    <?php $dayNumber++; ?>
                                <?php endforeach; ?>
                            </div>
                            <p class="fw-bold">Activities </p>
                            <div class="tab-content text-justify px-1">
                                <?php $detailNumber = 1 ?>
                                <?php foreach ($data['package_day'] as $day) : ?>
                                    <?php $i = 1; ?>
                                    <div class="tab-pane fade <?= $detailNumber == 1 ? "active show" : "" ?> " id="list-<?= $detailNumber ?>" role="tabpanel" aria-labelledby="list-<?= $detailNumber ?>-list">
                                        <?php foreach ($day['package_day_detail'] as $activities) : ?>
                                            <p><?= esc($i) . '. ' . esc($activities['detailDescription']); ?></p>
                                            <?php $i++; ?>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php $detailNumber++; ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <!--Rating and Review Section-->
            <?= $this->include('web/layouts/review'); ?>
        </div>

        <div class="col-md-6 col-12">
            <!-- Object Location on Map -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title">Google Maps</h5>
                </div>

                <?= $this->include('web/layouts/map-body'); ?>

            </div>

            <!-- Object Media -->

        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('javascript') ?>

<script>
    const myModal = document.getElementById('videoModal');
    const videoSrc = document.getElementById('video-play').getAttribute('data-src');

    myModal.addEventListener('shown.bs.modal', () => {
        console.log(videoSrc);
        document.getElementById('video').setAttribute('src', videoSrc);
    });
    myModal.addEventListener('hide.bs.modal', () => {
        document.getElementById('video').setAttribute('src', '');
    });
</script>
<script>
    let latBefore = ''
    let lngBefore = ''

    getObjectsByPackageDayId('<?= $data['package_day'][0]['day'] ?>')

    function getObjectsByPackageDayId(id_day) {
        $.ajax({
            url: `<?= base_url('api'); ?>/objects/package_day/${id_day}`,
            type: "GET",
            async: false,
            contentType: "application/json",
            success: function(response) {
                let objects = response['data']
                getObjectById(objects)
            },
            error: function(err) {
                console.log(err.responseText)
            }
        });
    }

    function getObjectById(objects = null) {
        let objectNumber = 1
        let flightPlanCoordinates = []
        clearMarker()
        clearRadius()
        clearRoute()
        objects.forEach(object => {
            let id_object = object['id_object']

            let URI = "<?= base_url('api') ?>";

            if (id_object.charAt(0) == 'R') {
                URI = URI + '/rumahGadang/' + `${id_object}`
            } else if (id_object.charAt(0) == 'E') {
                URI = URI + '/event/' + `${id_object}`
            } else if (id_object.charAt(0) == 'C') {
                URI = URI + '/culinaryPlace/' + `${id_object}`
            } else if (id_object.charAt(0) == 'W') {
                URI = URI + '/worshipPlace/' + `${id_object}`
            } else if (id_object.charAt(0) == 'S') {
                URI = URI + '/souvenirPlace/' + `${id_object}`
            }

            console.log(URI)
            currentUrl = '';
            $.ajax({
                url: URI,
                type: "GET",
                async: false,
                dataType: 'json',
                success: function(response) {
                    let data = response.data
                    currentUrl = currentUrl + data.id
                    // flightPlanCoordinates.push(new google.maps.LatLng(data.lat, data.lng))
                    showObjectOnMap(objectNumber, data.id, data.lat, data.lng)
                    boundToObject()

                }
            })
            objectNumber++
        })
        // flightPath(flightPlanCoordinates)
    }


    // Display marker for loaded object
    function showObjectOnMap(objectNumber, id, lat, lng, anim = true) {

        google.maps.event.clearListeners(map, 'click');
        let pos = new google.maps.LatLng(lat, lng);
        let marker = new google.maps.Marker();
        let icon = `https://raw.githubusercontent.com/Concept211/Google-Maps-Markers/master/images/marker_red${objectNumber}.png`;

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
        if (objectNumber == 1) {
            latBefore = lat
            lngBefore = lng
        } else {
            routeAll(lat, lng)
        }
    }

    // function flightPath(flightPlanCoordinates) {
    //     let result = new google.maps.Polyline({
    //         path: flightPlanCoordinates,
    //         strokeColor: "#FF0000",
    //         strokeOpacity: 1.0,
    //         strokeWeight: 2
    //     });
    //     result.setMap(map)
    // }

    function routeAll(lat, lng) {
        google.maps.event.clearListeners(map, 'click')

        let start, end;
        start = new google.maps.LatLng(latBefore, lngBefore);
        end = new google.maps.LatLng(lat, lng)
        let request = {
            origin: start,
            destination: end,
            travelMode: 'DRIVING'
        };
        directionsService.route(request, function(result, status) {
            if (status == 'OK') {
                directionsRenderer = new google.maps.DirectionsRenderer({
                    suppressMarkers: true
                })
                directionsRenderer.setDirections(result);
                // showSteps(result);
                directionsRenderer.setMap(map);
                routeArray.push(directionsRenderer);
            }
        });
        boundToRoute(start, end);
    }

    function showReservationModal() {
        <?php if (in_groups('user')) : ?>
            $('#modalTitle').html("Reservation")
            $('#modalBody').html(`
            <label for="reservation_date" class="mb-2">Reservation date </label>
            <input type="date" id="reservation_date" class="form-control" required >
            `)
            $('#modalFooter').html(`<a class="btn btn-success" onclick="makeReservation()"> Make reservation </a>`)
        <?php else : ?>
            $('#modalTitle').html('Login required')
            $('#modalBody').html('Login as user for reservation')
            $('#modalFooter').html(`<a class="btn btn-primary" href="/login"> Login </a> <a class="btn btn-primary" href="/regiter"> Register </a>`)
        <?php endif; ?>
    }

    function makeReservation() {
        let reservationDate = $("#reservation_date").val()
        if (!reservationDate) {
            Swal.fire('Please select reservation date', '', 'warning');
        } else {
            <?php if (in_groups('user')) : ?>
                let requestData = {
                    id_user: '<?= user()->id; ?>',
                    id_package: '<?= $data['id'] ?>',
                    id_reservation_status: 1, // pending status
                    reservation_date: reservationDate
                }
                $.ajax({
                    url: `<?= base_url('api'); ?>/reservation`,
                    type: "POST",
                    data: requestData,
                    async: false,
                    contentType: "application/json",
                    success: function(response) {
                        Swal.fire(
                            'Success to make reservation request',
                            '',
                            'success'
                        ).then(() => {
                            window.location.replace(baseUrl + '/web/reservation/' + <?= user()->id; ?>)
                        });

                    },
                    error: function(err) {
                        console.log(err.responseText)
                    }
                });
            <?php endif; ?>
        }
    }
</script>
<?= $this->endSection() ?>