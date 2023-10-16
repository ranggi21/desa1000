<?php

namespace App\Controllers\Web;

use App\Models\PackageModel;
use App\Models\PackageDayModel;
use App\Models\DetailPackageModel;
use App\Models\ServiceModel;
use App\Models\HomestayModel;
use App\Models\ReviewModel;
use App\Models\DetailServicePackageModel;
use CodeIgniter\RESTful\ResourcePresenter;
use CodeIgniter\Files\File;

class Package extends ResourcePresenter
{
    protected $PackageModel;
    protected $packageDayModel;
    protected $detailPackageModel;
    protected $ServiceModel;
    protected $HomestayModel;
    protected $DetailServicePackageModel;
    protected $ReviewModel;
    protected $helpers = ['auth', 'url', 'filesystem'];

    public function __construct()
    {
        $this->PackageModel = new PackageModel();
        $this->packageDayModel = new PackageDayModel();
        $this->detailPackageModel = new DetailPackageModel();
        $this->ServiceModel = new ServiceModel();
        $this->HomestayModel = new HomestayModel();
        $this->DetailServicePackageModel = new DetailServicePackageModel();
        $this->ReviewModel = new ReviewModel();
    }
    /**
     * Present a view of resource objects
     *
     * @return mixed
     */
    public function index()
    {
        $contents = $this->PackageModel->get_list_tp_api()->getResultArray();
        $data = [
            'title' => 'Tourism Package',
            'data' => $contents,
        ];

        return view('web/list_package', $data);
    }

    /**
     * Present a view to present a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $package = $this->PackageModel->get_tp_by_id_api($id)->getRowArray();
        if (empty($package)) {
            return redirect()->to(substr(current_url(), 0, -strlen($id)));
        }

        // avg rating
        $avg_rating = $this->ReviewModel->get_rating('id_package', $id)->getRowArray()['avg_rating'];
        // review
        $list_review = $this->ReviewModel->get_review_object_api('id_package', $id)->getResultArray();

        // service
        $list_service = $this->DetailServicePackageModel->get_service_by_package_api($id)->getResultArray();
        $services = array();
        foreach ($list_service as $service) {
            $services[] = $service['name'];
        }

        // package day
        $package_day = $this->packageDayModel->get_pd_by_package_id_api($id)->getResultArray();

        for ($i = 0; $i < count($package_day); $i++) {
            $package_day[$i]['package_day_detail'] = $this->detailPackageModel->get_detail_package_by_dp_api($package_day[$i]['id'])->getResultArray();
        }

        $package['avg_rating'] = $avg_rating;
        $package['services'] = $services;
        $package['reviews'] = $list_review;
        $package['package_day'] = $package_day;
        $package['gallery'] = [$package['url']];
        $package['video_url'] = null;


        $data = [
            'title' => $package['name'],
            'data' => $package,
        ];

        if (url_is('*dashboard*')) {
            return view('dashboard/detail_package', $data);
        }

        return view('web/detail_package', $data);
    }

    /**
     * Present a view to present a new single resource object
     *
     * @return mixed
     */
    public function new()
    {

        $homestayData = $this->HomestayModel->get_list_hm_api()->getResultArray();
        $serviceData = $this->ServiceModel->get_list_s_api()->getResultArray();

        $data = [
            'title' => 'New Package',
            'homestayData' => $homestayData,
            'serviceData' => $serviceData
        ];
        return view('dashboard/package_form', $data);
    }

    /**
     * Process the creation/insertion of a new resource object.
     * This should be a POST.
     *
     * @return mixed
     */
    public function create()
    {
        $request = $this->request->getPost();
        $id = $this->PackageModel->get_new_id_api();

        foreach ($request['packageDetailData'] as $packageDay => $value) {
            // dd($value);
            $packageDayId = $this->packageDayModel->get_new_id_api();
            $requestPackageDay = [
                'day' => $packageDayId,
                'id_package' => $id,
                'description' => $value['packageDayDescription']
            ];
            // $addPackageDay = $this->packageDayModel->add_pd_api($requestPackageDay);

            foreach ($value['detailPackage'] as $detailPackage => $dValue) {
                $detailPackageId = $this->detailPackageModel->get_new_id_api();
                d($dValue);

                // $requestDetailPackage = [
                //     'activity' => $detailPackageId,
                //     'id_day' => $packageDayId,
                //     'id_package' => $id,
                //     'id_object' => $detailPackage['id_object'],
                //     'activity_type' => $detailPackage['activity_type'],
                //     ''
                // ];
            }
        }
        die;
        $url = null;
        if (isset($request['gallery'])) {
            $folder = $request['gallery'][0];
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filename = get_filenames($filepath)[0];
            $fileImg = new File($filepath . '/' . $filename);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $url = $fileImg->getFilename();
        }
        $requestData = [
            'id' => $id,
            'name' => $request['name'],
            'id_homestay' => $request['id_homestay'],
            'price' => empty($request['price']) ? "0" : $request['price'],
            'capacity' => $request['capacity'],
            'cp' => $request['cp'],
            'url' => $url,
            'description' => $request['description'],
        ];

        $addtp = $this->PackageModel->add_tp_api($requestData);


        $addService = true;
        if (isset($request['service_package'])) {
            $services = $request['service_package'];
            $addService = $this->DetailServicePackageModel->add_service_api($id, $services);
        }

        if (isset($request['packageDay'])) {
            foreach ($request['packageDay'] as $packageDay => $packageDetail) {
                $requestData = [
                    'day' => $this->packageDayModel->get_new_id_api(),
                    'id_package' => $id

                ];
            }
        }

        if ($addtp && $addService) {
            return redirect()->to(base_url('dashboard/package'));
        } else {
            return redirect()->back()->withInput();
        }
    }

    /**
     * Present a view to edit the properties of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        $package = $this->PackageModel->get_tp_by_id_api($id)->getRowArray();
        $homestayData = $this->HomestayModel->get_list_hm_api()->getResultArray();
        $serviceData = $this->ServiceModel->get_list_s_api()->getResultArray();
        $packageService = $this->DetailServicePackageModel->get_service_by_package_api($id)->getResultArray();

        $selectedFac = array();
        foreach ($packageService as $service) {
            $selectedFac[] = $service['name'];
        }

        $package['service_package'] =  $selectedFac;
        $package['gallery'] = [$package['url']];
        $package['video_url'] = null;

        $data = [
            'title' => 'Edit Package',
            'data' => $package,
            'homestayData' => $homestayData,
            'serviceData' => $serviceData
        ];
        return view('dashboard/package_form', $data);
    }

    /**
     * Process the updating, full or partial, of a specific resource object.
     * This should be a POST.
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $request = $this->request->getPost();
        $url = null;
        if (isset($request['gallery'])) {
            $folder = $request['gallery'][0];
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filename = get_filenames($filepath)[0];
            $fileImg = new File($filepath . '/' . $filename);
            $fileImg->move(FCPATH . 'media/photos');
            delete_files($filepath);
            rmdir($filepath);
            $url = $fileImg->getFilename();
        }
        $requestData = [
            'name' => $request['name'],
            'id_homestay' => $request['id_homestay'],
            'price' => empty($request['price']) ? "0" : $request['price'],
            'capacity' => $request['capacity'],
            'cp' => $request['cp'],
            'url' => $url,
            'description' => $request['description'],
        ];
        $updateTp = $this->PackageModel->update_tp_api($id, $requestData);

        $addService = true;

        if (isset($request['service_package'])) {
            $services = $request['service_package'];
            $addService = $this->DetailServicePackageModel->update_service_api($id, $services);
        }

        if ($updateTp && $addService) {
            return redirect()->to(base_url('dashboard/package'));
        } else {
            return redirect()->back()->withInput();
        }
    }

    /**
     * Present a view to confirm the deletion of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function remove($id = null)
    {
        //
    }

    /**
     * Process the deletion of a specific resource object
     *
     * @param mixed $id
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        //
    }
}
