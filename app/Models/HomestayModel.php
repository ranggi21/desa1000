<?php

namespace App\Models;

use CodeIgniter\Model;

class HomestayModels extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'homestay';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'id_user','name','description','url'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;


 // API
 public function get_list_hm_api() {
    $query = $this->db->table($this->table)
        ->select('id,id_user,name,description,url')
        ->get();
    return $query;
}

public function get_hm_by_id_api($id = null) {
    $query = $this->db->table($this->table)
        ->select('id as id, homestay')
        ->where('id', $id)
        ->get();
    return $query;
}

public function get_new_id_api() {
    $lastId = $this->db->table($this->table)->select('id')->orderBy('id', 'ASC')->get()->getLastRow('array');
    if($lastId !=null){
        $count = (int)substr($lastId['id'], 0);
        $id = sprintf('%02d', $count + 1);

    }else{
        $count = 0;
        $id = sprintf('%02d', $count + 1);
    }
    return $id;
}

public function add_hm_api($homestay = null) {
    foreach ($homestay as $key => $value) {
        if(empty($value)) {
            unset($homestay[$key]);
        }
    }
    $homestay['created_at'] = Time::now();
    $homestay['updated_at'] = Time::now();
    $insert = $this->db->table($this->table)
        ->insert($homestay);
    return $insert;
}

public function update_hm_api($id = null, $homestay = null) {
    foreach ($homestay as $key => $value) {
        if(empty($value)) {
            unset($homestay[$key]);
        }
    }
    $homestay['updated_at'] = Time::now();
    $query = $this->db->table($this->table)
        ->where('id', $id)
        ->update($homestay);
    return $query;
}
}
