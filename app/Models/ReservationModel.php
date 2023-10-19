<?php

namespace App\Models;

use CodeIgniter\Model;
use CodeIgniter\I18n\Time;

class ReservationModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'reservation';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['id', 'id_user', 'id_package', 'id_reservation_status', 'request_date', 'deposit', 'total_price'];

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
    public function get_list_r_api()
    {
        $query = $this->db->table($this->table)
            ->select('*')
            ->get();
        return $query;
    }

    public function get_r_by_id_user_api($id_user = null)
    {
        $query = $this->db->table($this->table)
            ->select('*')
            ->where('id_user', $id_user)
            ->orderBy('id', 'DESC')
            ->get();
        return $query;
    }

    public function get_new_id_api()
    {
        $lastId = $this->db->table($this->table)->select('id')->orderBy('id', 'ASC')->get()->getLastRow('array');

        if ($lastId != null) {
            $count = (int)substr($lastId['id'], 2);
            $id = sprintf('RS%02d', $count + 1);
        } else {
            $count = 0;
            $id = sprintf('RS%02d', $count + 1);
        }
        return $id;
    }

    public function add_r_api($reservation = null)
    {
        foreach ($reservation as $key => $value) {
            if (empty($value)) {
                unset($facility[$key]);
            }
        }
        $reservation['created_at'] = Time::now();
        $reservation['updated_at'] = Time::now();
        $insert = $this->db->table($this->table)
            ->insert($reservation);
        return $insert;
    }

    public function update_r_api($id = null, $data = null)
    {
        foreach ($data as $key => $value) {
            if (empty($value)) {
                unset($data[$key]);
            }
        }
        $data['updated_at'] = Time::now();
        $query = $this->db->table($this->table)
            ->where('id', $id)
            ->update($data);
        return $query;
    }
}
