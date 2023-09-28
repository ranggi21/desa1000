<?php

namespace App\Controllers\Web;

use App\Models\PackageModel;
use App\Models\ServiceModel;
use App\Models\HomestayModel;
use CodeIgniter\RESTful\ResourcePresenter;

class Package extends ResourcePresenter
{
    protected $PackageModel;
    protected $ServiceModel;
    protected $HomestayModel;
    protected $helpers = ['auth', 'url', 'filesystem'];
    
    public function __construct()
    {
        $this->PackageModel = new PackageModel();
        $this->ServiceModel = new ServiceModel();
        $this->HomestayModel = new HomestayModel();
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
        //
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
        $requestData = [
            'id' => $id,
            'name' => $request['name'],
            'id_homestay' => $request['id_homestay'],
            'price' => empty($request['ticket_price']) ? "0" : $request['ticket_price'],
            'capacity' => $request['capacity'],
            'cp' => $request['cp'],
            'url' => $request['url'],
            'description' => $request['description'],
        ];
       
        $addtp = $this->PackageModel->add_tp_api($requestData);
        if ($addtp) {  
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
        $Package = $this->PackageModel->get_tp_by_id_api($id)->getRowArray();
        $Homestay = [
            "id" => [1,2,3],
            "name" => ["Satu", "Dua", "Tiga"]
        ];
        $data = [
            'title' => 'Edit Package',
            'data' => $Package,
            'homestayData' => $Homestay
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
        $requestData = [
            'Package' => $request['Package'],
        ];
        $updatetp = $this->PackageModel->update_tp_api($id, $requestData);
        if ($updatetp) {
            return redirect()->to(base_url('dashboard/Package'));
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
