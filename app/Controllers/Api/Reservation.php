<?php

namespace App\Controllers\Api;

use App\Models\ReservationModel;
use CodeIgniter\API\ResponseTrait;
use CodeIgniter\I18n\Time;
use CodeIgniter\RESTful\ResourceController;
use DateTime;

class Reservation extends ResourceController
{
    use ResponseTrait;

    protected $reservationModel;

    public function __construct()
    {
        $this->reservationModel = new ReservationModel();
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $contents = $this->reservationModel->get_list_s_api()->getResult();
        $response = [
            'data' => $contents,
            'status' => 200,
            'message' => [
                "Success get list of Service"
            ]
        ];
        return $this->respond($response);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        //
    }

    /**
     * Return a new resource object, with default properties
     *
     * @return mixed
     */
    public function new()
    {
        //
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $request = $this->request->getRawInput();

        $id = $this->reservationModel->get_new_id_api();
        $requestData = [
            'id' => $id,
            'id_user' => $request['id_user'],
            'id_package' => $request['id_package'],
            'id_reservation_status' => $request['id_reservation_status'],
            'request_date' => $request['reservation_date']
        ];

        $addR = $this->reservationModel->add_r_api($requestData);
        if ($addR) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success create new reservation"
                ]
            ];
            return $this->respondCreated($response);
        } else {
            $response = [
                'status' => 400,
                'message' => [
                    "Fail create new reservation",
                ]
            ];
            return $this->respond($response, 400);
        }
    }

    /**
     * Return the editable properties of a resource object
     *
     * @return mixed
     */
    public function edit($id = null)
    {
        //
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $request = $this->request->getRawInput();
        $requestData = [
            'id_reservation_status' => $request['id_reservation_status'],
            'id_user' => $request['id_user'],
            'id_package' => $request['id_package'],
            'request_date' => $request['request_date']
        ];

        $updateFC = $this->reservationModel->update_r_api($id, $requestData);
        if ($updateFC) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success update Service"
                ]
            ];
            return $this->respondCreated($response);
        } else {
            $response = [
                'status' => 400,
                'message' => [
                    "Fail update Service",
                ]
            ];
            return $this->respond($response, 400);
        }
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $delete = $this->reservationModel->delete(['id' => $id]);
        if ($delete) {
            $response = [
                'status' => 200,
                'message' => [
                    "Success delete Reservation"
                ]
            ];
            return $this->respondDeleted($response);
        } else {
            $response = [
                'status' => 404,
                'message' => [
                    "Reservation not found"
                ]
            ];
            return $this->failNotFound('' + $response);
        }
    }
}
