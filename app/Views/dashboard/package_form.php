<?php
$uri = service('uri')->getSegments();
$edit = in_array('edit', $uri);
?>

<?= $this->extend('dashboard/layouts/main'); ?>

<?= $this->section('styles') ?>
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/filepond-plugin-media-preview@1.0.11/dist/filepond-plugin-media-preview.min.css">
<link rel="stylesheet" href="<?= base_url('assets/css/pages/form-element-select.css'); ?>">
<style>
    .filepond--root {
        width: 100%;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<section class="section">
    <form class="form form-horizontal" action="<?= ($edit) ? base_url('dashboard/package/update') . '/' . $data['id'] : base_url('dashboard/package'); ?>" method="post" onsubmit="checkRequired(event)" enctype="multipart/form-data">
        <div class="form-body">
            <div class="row">
                <script>
                    currentUrl = '<?= current_url(); ?>';
                </script>
                <!-- Object Detail Information -->
                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title text-center"><?= $title; ?></h4>
                        </div>
                        <div class="card-body">

                            <div class="form-group mb-4">
                                <label for="name" class="mb-2">Tourism Package Name</label>
                                <input type="text" id="name" class="form-control" name="name" placeholder="Tourism Package Name" value="<?= ($edit) ? $data['name'] : old('name'); ?>" required>
                            </div>
                            <fieldset class="form-group mb-4">
                                <label for="id_homestay" class="mb-2">Homestay</label>
                                <select class="form-select" id="id_homestay" name="id_homestay">
                                    <?php if ($homestayData) : ?>
                                        <?php foreach ($homestayData as $homestay) : ?>
                                            <?php if ($edit && $homestay['id'] && $data['id_homestay']) : ?>
                                                <option value="<?= $homestay['id'] ?>" <?= (esc($homestay['id']) == $data['id_homestay']) ? 'selected' : ''; ?>><?= $homestay['name']; ?></option>
                                            <?php else : ?>
                                                <option value="<?= esc($homestay['id']); ?>"><?= esc($homestay['name']); ?></option>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    <?php else : ?>
                                        <option value=" ">Homestay not found</option>
                                    <?php endif; ?>
                                </select>
                            </fieldset>
                            <div class="form-group mb-4">
                                <label for="price" class="mb-2">Price</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp </span>
                                    <input type="number" id="price" class="form-control" name="price" placeholder="price" aria-label="price" aria-describedby="price" value="<?= ($edit) ? $data['price'] : old('price'); ?>">
                                </div>
                            </div>
                            <div class="form-group mb-4">
                                <label for="capacity" class="mb-2">Capacity</label>
                                <input type="tel" id="capacity" class="form-control" name="capacity" placeholder="capacity" value="<?= ($edit) ? $data['capacity'] : old('capacity'); ?>">
                            </div>
                            <div class="form-group mb-4">
                                <label for="contact_person" class="mb-2">Contact Person</label>
                                <input type="tel" id="contact_person" class="form-control" name="cp" placeholder="Contact Person" value="<?= ($edit) ? $data['contact_person'] : old('cp'); ?>">
                            </div>

                            <div class="form-group mb-4">
                                <label for="service_package" class="mb-2">Service Package</label>
                                <select class="choices form-select multiple-remove" multiple="multiple" id="service_package" name="service_package[]">
                                    <?php foreach ($serviceData as $service) : ?>
                                        <?php if ($edit && in_array(esc($service['name']), $data['service_package'])) : ?>
                                            <option value="<?= esc($service['id']); ?>" selected><?= esc($service['name']); ?></option>
                                        <?php else : ?>
                                            <option value="<?= esc($service['id']); ?>"><?= esc($service['name']); ?></option>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label for="description" class="form-label">Description</label>
                                <textarea class="form-control" id="description" name="description" rows="4"><?= ($edit) ? $data['description'] : old('description'); ?></textarea>
                            </div>
                            <div class="form-group mb-4">
                                <label for="gallery" class="form-label">Brosur url</label>
                                <input class="form-control" accept="image/*" type="file" name="gallery[]" id="gallery">
                            </div>

                            <button type="submit" class="btn btn-primary me-1 mb-1">Submit</button>
                            <button type="reset" class="btn btn-light-secondary me-1 mb-1">Reset</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title text-center"> Detail package</h4>
                        </div>
                        <div class="card-body">
                            <button type="button" class="btn btn-outline-primary block" data-bs-toggle="modal" data-bs-target="#default">
                                New package day
                            </button>
                            <div class="p-4" id="package-day-container">

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</section>
<?= $this->endSection() ?>
<?= $this->section('javascript') ?>
<script src="https://unpkg.com/filepond-plugin-file-validate-size/dist/filepond-plugin-file-validate-size.js"></script>
<script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-exif-orientation/dist/filepond-plugin-image-exif-orientation.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
<script src="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.js"></script>
<script src="https://cdn.jsdelivr.net/npm/filepond-plugin-media-preview@1.0.11/dist/filepond-plugin-media-preview.min.js"></script>
<script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>
<script src="<?= base_url('assets/js/extensions/form-element-select.js'); ?>"></script>


<script>
    FilePond.registerPlugin(
        FilePondPluginFileValidateSize,
        FilePondPluginFileValidateType,
        FilePondPluginImageExifOrientation,
        FilePondPluginImagePreview,
        FilePondPluginImageResize,
        FilePondPluginMediaPreview,
    );

    // Get a reference to the file input element
    const photo = document.querySelector('input[id="gallery"]');
    const video = document.querySelector('input[id="video"]');


    // Create a FilePond instance
    const pond = FilePond.create(photo, {
        maxFileSize: '1920MB',
        maxTotalFileSize: '1920MB',
        imageResizeTargetHeight: 720,
        imageResizeUpscale: false,
        credits: false,
    });
    const vidPond = FilePond.create(video, {
        maxFileSize: '1920MB',
        maxTotalFileSize: '1920MB',
        credits: false,
    })


    <?php if ($edit &&  strlen($data['gallery'][0])) : ?>
        pond.addFiles(
            "<?= base_url('media/photos/'); ?>/<?= $data['gallery'][0]; ?>"
        );
    <?php endif; ?>
    pond.setOptions({
        server: {
            timeout: 3600000,
            process: {
                url: '/upload/photo',
                onload: (response) => {
                    console.log("processed:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
            revert: {
                url: '/upload/photo',
                onload: (response) => {
                    console.log("reverted:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
        }
    });

    <?php if ($edit && $data['video_url'] != null) : ?>
        vidPond.addFile(`<?= base_url('media/videos/' . $data['video_url']); ?>`)
    <?php endif; ?>
    vidPond.setOptions({
        server: {
            timeout: 86400000,
            process: {
                url: '/upload/video',
                onload: (response) => {
                    console.log("processed:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
            revert: {
                url: '/upload/video',
                onload: (response) => {
                    console.log("reverted:", response);
                    return response
                },
                onerror: (response) => {
                    console.log("error:", response);
                    return response
                },
            },
        }
    });
</script>
<script>
    let day = 0;

    function newPackageDayTable(packageDayDescription) {
        return
    }

    function addPackageDay() {
        let packageDayDescription = $("#package-day-description").val()
        day++;
        $("#package-day-container").append(`
        <div class="border shadow-sm p-2 mb-2">
        <span> Day </span>  <input type="text" value="${day}" name="packageDetailData[${day}][day]"  class="d-block"> 
        <span> Description </span>  <input value="${packageDayDescription}" name="packageDetailData[${day}][packageDayDescription]" class="d-block">  
        <br>
        <br>
        <a class="btn btn-outline-success btn-sm" onclick="addDetailPackage('${day}')" data-bs-toggle="modal" data-bs-target="#detailPackageModal"> <i class="fa fa-plus"> </i> </a>
        <table class="table table-border" id="table-day">
            <thead>
                <tr>
                    <th>Object name</th>
                    <th>Activity type</th>
                    <th>Description</th>
                </tr>
            </thead>
            <tbody id="body-detail-package-${day}">

            </tbody>     
        </table>
        </div>`)

    }
    let noDetail = 0;

    function saveOnPackageDayTable() {
        let day = $("#detail-package-day").val()
        let object_id = $("#detail-package-id-object").val()
        let activity_type = $("#detail-package-activity-type").val()
        let description = $("#detail-package-description").val()

        $(`#body-detail-package-${day}`).append(`
        <tr> 
          <td><input value="${object_id}" name="packageDetailData[${day}][detailPackage][id_object][]"></td>
          <td><input value="${activity_type}" name="packageDetailData[${day}][detailPackage][activity][]"></td>
          <td><input value="${description}" name="packageDetailData[${day}][detailPackage][description][]"></td>
        </tr>
        `)
        noDetail++;
    }

    function addDetailPackage(day) {
        $("#detailPackageModalHeader").html(`Add Day ${day} Detail`)
        $("#detailPackageModalBody").html(`
        <input type="text" id="detail-package-day" class="form-control" name="detail-package-day" value="${day}" disabled placeholder="object" required>
        <div class="form-group mb-4">
                    <label for="detail-package-id_object" class="mb-2">Object</label>
                    <input type="text" id="detail-package-id-object" class="form-control" name="detail-package-id-object" placeholder="object" required>
        </div>
        <div class="form-group mb-4">
                    <label for="detail-package-activity-type" class="mb-2">Activity type</label>
                    <input type="text" id="detail-package-activity-type" class="form-control" name="detail-package-activity-type" placeholder="activity type" required>
        </div>
        <div class="form-group mb-4">
                    <label for="detail-package-description" class="mb-2">Description</label>
                    <input type="text" id="detail-package-description" class="form-control" name="detail-package-description" placeholder="Detail package description" required>
        </div>
        `)
    }
</script>
<!-- modal -->
<div class="modal fade text-left" id="default" tabindex="-1" aria-labelledby="myModalLabel1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel1">New package day</h5>
                <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group mb-4">
                    <label for="name" class="mb-2">Description</label>
                    <input type="text" id="package-day-description" class="form-control" name="description" placeholder="package day description" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">
                    <i class="bx bx-x d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Close</span>
                </button>
                <button type="button" onclick="addPackageDay()" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>
<!-- modal detail -->
<div class="modal fade text-left" id="detailPackageModal" tabindex="-1" aria-labelledby="myModalLabel1" style="display: none;" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailPackageModalHeader"> </h5>
                <button type="button" class="close rounded-pill" data-bs-dismiss="modal" aria-label="Close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
            <div class="modal-body" id="detailPackageModalBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn" data-bs-dismiss="modal">
                    <i class="bx bx-x d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Close</span>
                </button>
                <button type="button" onclick="saveOnPackageDayTable()" class="btn btn-primary ml-1" data-bs-dismiss="modal">
                    <i class="bx bx-check d-block d-sm-none"></i>
                    <span class="d-none d-sm-block">Save</span>
                </button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>