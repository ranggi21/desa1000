<?php

namespace App\Controllers\Web;

use App\Models\CategoryEventModel;
use App\Models\GalleryAtractionModel;
use App\Models\ReviewModel;
use App\Models\AtractionModel;
use CodeIgniter\Files\File;
use CodeIgniter\RESTful\ResourcePresenter;

class Atraction extends ResourcePresenter
{
    protected $atractionModel;
    protected $atractionGalleryModel;
    protected $categoryEventModel;
    protected $reviewModel;
    protected $helpers = ['auth', 'url', 'filesystem'];

    public function __construct()
    {
        $this->atractionModel = new AtractionModel();
        $this->atractionGalleryModel = new GalleryAtractionModel();
        $this->reviewModel = new ReviewModel();
    }

    /**
     * Present a view of resource objects
     *
     * @return mixed
     */
    public function index()
    {
        $contents = $this->atractionModel->get_list_up_api()->getResultArray();
        $data = [
            'title' => 'atraction ',
            'data' => $contents,
        ];

        return view('web/list_atraction_', $data);
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
        $atraction = $this->atractionModel->get_up_by_id_api($id)->getRowArray();
        if (empty($atraction)) {
            return redirect()->to(substr(current_url(), 0, -strlen($id)));
        }

        $avg_rating = $this->reviewModel->get_rating('id_atraction', $id)->getRowArray()['avg_rating'];
        $list_review = $this->reviewModel->get_review_object_api('id_atraction', $id)->getResultArray();

        $list_gallery = $this->atractionGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }


        $atraction['avg_rating'] = $avg_rating;
        $atraction['reviews'] = $list_review;
        $atraction['gallery'] = $galleries;

        $data = [
            'title' => $atraction['name'],
            'data' => $atraction,
        ];

        if (url_is('*dashboard*')) {
            return view('dashboard/detail_atraction', $data);
        }
        return view('web/detail_atraction', $data);
    }

    /**
     * Present a view to present a new single resource object
     *
     * @return mixed
     */
    public function new()
    {
        $categories = $this->categoryEventModel->get_list_cat_api()->getResultArray();
        $data = [
            'title' => 'New atraction ',
            'categories' => $categories
        ];
        return view('dashboard/atraction__form', $data);
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
        $id = $this->atractionModel->get_new_id_api();
        $requestData = [
            'id_atraction' => $id,
            'name' => $request['name'],
            'address' => $request['address'],
            'description' => $request['description'],
            'cp' => $request['contact_person'],
            'status' => $request['category'],
            'id_user' => $request['owner'],
            'lat' => $request['lat'],
            'lng' => $request['lng'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        $geojson = $request['geo-json'];
        if (isset($request['video'])) {
            $folder = $request['video'];
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $vidFile = new File($filepath . '/' . $filenames[0]);
            $vidFile->move(FCPATH . 'media/videos');
            delete_files($filepath);
            rmdir($filepath);
            $requestData['video_url'] = $vidFile->getFilename();
        }
        $addEV = $this->atractionModel->add_up_api($requestData, $geojson);

        if (isset($request['gallery'])) {
            $folders = $request['gallery'];
            $gallery = array();
            foreach ($folders as $folder) {
                $filepath = WRITEPATH . 'uploads/' . $folder;
                $filenames = get_filenames($filepath);
                $fileImg = new File($filepath . '/' . $filenames[0]);
                $fileImg->move(FCPATH . 'media/photos');
                delete_files($filepath);
                rmdir($filepath);
                $gallery[] = $fileImg->getFilename();
            }
            $this->atractionGalleryModel->add_gallery_api($id, $gallery);
        }

        if ($addEV) {
            return redirect()->to(base_url('dashboard/atraction') . '/' . $id);
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
        $atraction = $this->atractionModel->get_up_by_id_api($id)->getRowArray();
        if (empty($atraction)) {
            return redirect()->to('dashboard/atraction');
        }

        $list_gallery = $this->atractionGalleryModel->get_gallery_api($id)->getResultArray();
        $galleries = array();
        foreach ($list_gallery as $gallery) {
            $galleries[] = $gallery['url'];
        }

        $categories = $this->categoryEventModel->get_list_cat_api()->getResultArray();

        $atraction['gallery'] = $galleries;
        $data = [
            'title' => 'Edit atraction ',
            'data' => $atraction,
            'categories' => $categories
        ];
        return view('dashboard/atraction__form', $data);
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
            'name' => $request['name'],
            'address' => $request['address'],
            'open' => $request['open'],
            'close' => $request['close'],
            'description' => $request['description'],
            'cp' => $request['contact_person'],
            'lat' => $request['lat'],
            'lng' => $request['lng'],
        ];
        foreach ($requestData as $key => $value) {
            if (empty($value)) {
                unset($requestData[$key]);
            }
        }
        $geojson = $request['geo-json'];
        if (isset($request['video'])) {
            $folder = $request['video'];
            $filepath = WRITEPATH . 'uploads/' . $folder;
            $filenames = get_filenames($filepath);
            $vidFile = new File($filepath . '/' . $filenames[0]);
            $vidFile->move(FCPATH . 'media/videos');
            delete_files($filepath);
            rmdir($filepath);
            $requestData['video_url'] = $vidFile->getFilename();
        } else {
            $requestData['video_url'] = null;
        }
        $updateEV = $this->atractionModel->update_up_api($id, $requestData, $geojson);

        if (isset($request['gallery'])) {
            $folders = $request['gallery'];
            $gallery = array();
            foreach ($folders as $folder) {
                $filepath = WRITEPATH . 'uploads/' . $folder;
                $filenames = get_filenames($filepath);
                $fileImg = new File($filepath . '/' . $filenames[0]);
                $fileImg->move(FCPATH . 'media/photos');
                delete_files($filepath);
                rmdir($filepath);
                $gallery[] = $fileImg->getFilename();
            }
            $this->atractionGalleryModel->update_gallery_api($id, $gallery);
        } else {
            $this->atractionGalleryModel->delete_gallery_api($id);
        }

        if ($updateEV) {
            return redirect()->to(base_url('dashboard/atraction') . '/' . $id);
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
