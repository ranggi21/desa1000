<?= $this->extend('profile/index'); ?>

<?= $this->section('content') ?>
<!-- Modal  -->
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
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Reservation</h3>
            <a title="find package" class="btn btn-primary" href="<?= base_url('web') ?>/package "> List package <i class="fa fa-search"></i></a>
        </div>
        <div class="card-body">
            <table class="table table-border ">
                <thead>
                    <tr>
                        <th class="text-start"> #</th>
                        <th class="text-start"> Tourism package name </th>
                        <th class="text-start"> Reservation date </th>
                        <th class="text-start"> Status </th>
                        <th class="text-start"> Action </th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php foreach ($data as $item) : ?>
                        <tr>
                            <td class="text-start"> <?= $no; ?> </td>
                            <td class="text-start"> <?= $item['package_name']; ?> </td>
                            <td class="text-start"> <?= $item['request_date']; ?> </td>
                            <td class="text-start">
                                <span class="badge bg-<?php if ($item['id_reservation_status'] == 1) {
                                                            echo "warning";
                                                        } else if ($item['id_reservation_status'] == 2) {
                                                            echo "success";
                                                        } else if ($item['id_reservation_status'] == 3) {
                                                            echo "danger";
                                                        }; ?>">
                                    <?= $item['status']; ?>
                                </span>
                            </td>
                            <td>
                                <a class="btn btn-success"><i class="fa fa-ticket" title="deposit"></i> </a>
                                <?php if ($item['id_reservation_status'] != 3) : ?>
                                    <a title="cancel reservation" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#reservationModal" onclick="showModalDelete('<?= $item['id'] ?>','<?= $item['id_user'] ?>','<?= $item['id_package'] ?>','<?= $item['request_date'] ?>')"> <i class="fa fa-x"></i></a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php $no++; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>

        </div>
    </div>
</section>
<?= $this->endSection() ?>
<?= $this->section('javascript') ?>
<script>
    function showModalDelete(id, id_user, id_package, request_date) {
        $('#modalTitle').html("Abort reservation")
        $('#modalBody').html(`Are you sure cancel this reservation?`)
        $('#modalFooter').html(`<a class="btn btn-danger" onclick="cancelReservation('${id}','${id_user}','${id_package}','${request_date}')"> Cancel </a>`)
    }

    function deleteReservation(id_reservation) {
        $.ajax({
            url: `<?= base_url('api'); ?>/reservation/${id_reservation}`,
            type: "DELETE",
            async: false,
            contentType: "application/json",
            success: function(response) {
                Swal.fire(
                    'Reservation deleted',
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
    }

    function cancelReservation(id, id_user, id_package, request_date) {
        let requestData = {
            id_user: id_user,
            id_package: id_package,
            id_reservation_status: 3, // cancel status
            request_date: request_date
        }
        console.log(requestData)
        $.ajax({
            url: `<?= base_url('api'); ?>/reservation/${id}`,
            type: "PUT",
            data: requestData,
            async: false,
            contentType: "application/json",
            success: function(response) {
                Swal.fire(
                    'Reservation Canceled',
                    '',
                    'success'
                ).then(() => {
                    window.location.replace(baseUrl + '/web/reservation/')
                });
            },
            error: function(err) {
                console.log(err.responseText)
            }
        });
    }
</script>

<?= $this->endSection() ?>