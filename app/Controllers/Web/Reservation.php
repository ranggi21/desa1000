<?php

namespace App\Controllers\Web;

use App\Models\ReservationModel;
use App\Models\ReservationStatusModel;
use App\Models\PackageModel;
use CodeIgniter\RESTful\ResourcePresenter;

class Reservation extends ResourcePresenter
{
    protected $reservationModel;
    protected $reservationStatusModel;
    protected $packageModel;

    protected $helpers = ['auth', 'url', 'filesystem'];

    public function __construct()
    {
        $this->reservationModel = new ReservationModel();
        $this->reservationStatusModel = new ReservationStatusModel();
        $this->packageModel = new PackageModel();
    }
    /**
     * Present a view of resource objects
     *
     * @return mixed
     */
    public function index()
    {
        //
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
        if (url_is('*dashboard*')) {
            $users_reservation = $this->reservationModel->get_list_r_api()->getResultArray();
        } else {
            $users_reservation = $this->reservationModel->get_r_by_id_user_api($id)->getResultArray();
        }
        if (empty($users_reservation)) {
            return redirect()->to(substr(current_url(), 0, -strlen($id)));
        }

        $no = 0;

        // reservation status dan paket
        foreach ($users_reservation as $item) {
            $reservation_status_id = $item['id_reservation_status'];
            $package_id = $item['id_package'];
            $reservationStatus = $this->reservationStatusModel->get_s_by_id_api($reservation_status_id)->getRowArray();
            $package = $this->packageModel->get_tp_by_id_api($package_id)->getRowArray();
            $users_reservation[$no]['status'] = $reservationStatus['status'];
            $users_reservation[$no]['package_name'] = $package['name'];
            $no++;
        }


        $data = [
            'title' => 'User Reservation',
            'data' => $users_reservation,
        ];

        if (url_is('*dashboard*')) {
            return view('dashboard/reservation', $data);
        }

        return view('web/reservation', $data);
    }

    /**
     * Present a view to present a new single resource object
     *
     * @return mixed
     */
    public function new()
    {
        $id = $this->reservationModel->get_new_id_api();
        $data = [
            'title' => 'New Facility',
            'id' => $id
        ];
        return view('dashboard/facility_form', $data);
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
        $requestData = [
            'id_facility_rumah_gadang' => $request['id'],
            'facility' => $request['facility'],
        ];
        $addFC = $this->reservationModel->add_fc_api($requestData);
        if ($addFC) {
            return redirect()->to(base_url('dashboard/facility'));
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
        $facility = $this->reservationModel->get_fc_by_id_api($id)->getRowArray();
        $data = [
            'title' => 'Edit Facility',
            'data' => $facility
        ];
        return view('dashboard/facility_form', $data);
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
        $requestData = [
            'facility' => $request['facility'],
        ];
        $updateFC = $this->reservationModel->update_fc_api($id, $requestData);
        if ($updateFC) {
            return redirect()->to(base_url('dashboard/facility'));
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
