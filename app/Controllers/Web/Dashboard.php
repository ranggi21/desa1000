<?php

namespace App\Controllers\Web;

use App\Controllers\BaseController;
use App\Models\AccountModel;
use App\Models\EventModel;
use App\Models\FacilityRumahGadangModel;
use App\Models\PackageDayModel;
use App\Models\RumahGadangModel;
use App\Models\ReservationModel;
use App\Models\ReservationStatusModel;
use App\Models\PackageModel;
use App\Models\ServiceModel;
use App\Models\UniquePlaceModel;

class Dashboard extends BaseController
{
    protected $reservationModel;
    protected $reservationStatusModel;
    protected $rumahGadangModel;
    protected $packageModel;
    protected $serviceModel;
    protected $packageDayModel;
    protected $eventModel;
    protected $facilityModel;
    protected $accountModel;
    protected $uniquePlace;
    protected $helpers = ['auth'];

    public function __construct()
    {
        $this->rumahGadangModel = new RumahGadangModel();
        $this->reservationModel = new ReservationModel();
        $this->reservationStatusModel = new ReservationStatusModel();
        $this->packageModel = new PackageModel();
        $this->serviceModel = new ServiceModel();
        $this->packageDayModel = new PackageDayModel();
        $this->eventModel = new EventModel();
        $this->uniquePlace = new UniquePlaceModel();
        $this->facilityModel = new FacilityRumahGadangModel();
        $this->accountModel = new AccountModel();
    }
    public function index()
    {
        if (in_groups("owner")) {
            return redirect()->to(base_url('/dashboard/rumahGadang'));
        } elseif (in_groups("admin")) {

            return redirect()->to(base_url('/dashboard/dashboard'));
        }
        return redirect()->to(base_url('/web'));
    }

    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard',
            'category' => 'Dashboard Menu'
        ];
        return view('dashboard/index', $data);
    }
    public function rumahGadang()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->rumahGadangModel->get_list_rg_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $contents = $this->rumahGadangModel->list_by_owner_api(user()->id)->getResultArray();
        }

        $data = [
            'title' => 'Manage Rumah Gadang',
            'category' => 'Rumah Gadang',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function reservation()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->reservationModel->get_list_r_api()->getResultArray();
        }

        $no = 0;
        // reservation status dan paket
        foreach ($contents as $item) {
            $reservation_status_id = $item['id_reservation_status'];
            $package_id = $item['id_package'];
            $reservationStatus = $this->reservationStatusModel->get_s_by_id_api($reservation_status_id)->getRowArray();
            $package = $this->packageModel->get_tp_by_id_api($package_id)->getRowArray();
            $contents[$no]['status'] = $reservationStatus['status'];
            $contents[$no]['package_name'] = $package['name'];
            $no++;
        }
        $data = [
            'title' => 'Manage Reservation',
            'category' => 'Reservation',
            'data' => $contents,
        ];
        return view('dashboard/reservation', $data);
    }
    public function package()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->packageModel->get_list_tp_api()->getResultArray();
        }

        $data = [
            'title' => 'Manage Paket Wisata',
            'category' => 'Paket Wisata',
            'data' => $contents,
        ];

        return view('dashboard/manage', $data);
    }

    public function service()
    {
        $contents = $this->serviceModel->get_list_s_api()->getResultArray();
        $data = [
            'title' => 'Manage Service',
            'category' => 'Service',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }
    public function packageDay()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->packageDayModel->get_list_pd_api()->getResultArray();
        }

        $data = [
            'title' => 'Manage Package Day',
            'category' => 'Package Day',
            'data' => $contents,
        ];

        return view('dashboard/manage', $data);
    }

    public function event()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->eventModel->get_list_ev_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $contents = $this->eventModel->list_by_owner_api(user()->id)->getResultArray();
        }

        $data = [
            'title' => 'Manage Event',
            'category' => 'Event',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function uniquePlace()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->uniquePlace->get_list_up_api()->getResultArray();
        }

        $data = [
            'title' => 'Manage Unique Place',
            'category' => 'Unique Place',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function facility()
    {
        $contents = $this->facilityModel->get_list_fc_api()->getResultArray();
        $data = [
            'title' => 'Manage Facility',
            'category' => 'Facility',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function users()
    {
        $contents = $this->accountModel->get_list_user_api()->getResultArray();
        $data = [
            'title' => 'Manage Users',
            'category' => 'Users',
            'data' => $contents,
        ];
        return view('dashboard/manage', $data);
    }

    public function recommendation()
    {
        $contents = [];
        if (in_groups('admin')) {
            $contents = $this->rumahGadangModel->get_list_recommendation_api()->getResultArray();
        } elseif (in_groups('owner')) {
            $contents = $this->rumahGadangModel->recommendation_by_owner_api(user()->id)->getResultArray();
        }

        $recommendations = $this->rumahGadangModel->get_recommendation_data_api()->getResultArray();
        $data = [
            'title' => 'Manage Recommendation',
            'category' => 'Recommendation',
            'data' => $contents,
            'recommendations' => $recommendations,
        ];
        return view('dashboard/recommendation', $data);
    }
}
