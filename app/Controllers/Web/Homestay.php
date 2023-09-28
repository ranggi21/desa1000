<?php

namespace App\Controllers\Web;

use App\Models\HomestayModel;
use CodeIgniter\RESTful\ResourcePresenter;

class Homestay extends ResourcePresenter
{
    protected $HomestayModel;
    
    protected $helpers = ['auth', 'url', 'filesystem'];
    
    public function __construct()
    {
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
        $id = $this->HomestayModel->get_new_id_api();
        $data = [
            'title' => 'New Homestay',
            'id' => $id
        ];
        return view('dashboard/Homestay_form', $data);
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
            'id_Homestay_rumah_gadang' => $request['id'],
            'Homestay' => $request['Homestay'],
        ];
        $addFC = $this->HomestayModel->add_fc_api($requestData);
        if ($addFC) {
            return redirect()->to(base_url('dashboard/Homestay'));
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
        $Homestay = $this->HomestayModel->get_fc_by_id_api($id)->getRowArray();
        $data = [
            'title' => 'Edit Homestay',
            'data' => $Homestay
        ];
        return view('dashboard/Homestay_form', $data);
        
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
            'Homestay' => $request['Homestay'],
        ];
        $updateFC = $this->HomestayModel->update_fc_api($id, $requestData);
        if ($updateFC) {
            return redirect()->to(base_url('dashboard/Homestay'));
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
