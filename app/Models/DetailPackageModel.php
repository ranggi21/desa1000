<?php

namespace App\Models;

use CodeIgniter\I18n\Time;
use CodeIgniter\Model;

class DetailPackageModel extends Model
{
    protected $DBGroup          = 'default';
    protected $table            = 'detail_package';
    protected $primaryKey       = 'activity';
    protected $returnType       = 'array';
    // protected $allowedFields    = ['id_detail_service_package', 'id_service_package', 'id_package'];

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
    public function get_detail_package_by_package_api($day = null)
    {
        $query = $this->db->table($this->table)
            ->select('activity as id, id_day, id_package, id_object, activity_type, description')
            ->where('id_package', $day)
            ->join('tourism_package', 'detail_package.id_package = tourism_package.id')
            ->get();
        return $query;
    }

    public function get_detail_package_by_dp_api($package_id = null)
    {
        $query = $this->db->table($this->table)
            ->select('*')
            ->where('id_package', $package_id)
            ->get();
        return $query;
    }


    public function get_new_id_api()
    {
        $lastId = $this->db->table($this->table)->select('activity ')->orderBy('activity', 'ASC')->get()->getLastRow('array');
        if ($lastId != null) {
            $count = (int)substr($lastId['activity'], 0);
            $id = sprintf('DP%02d', $count + 1);
        } else {
            $count = 0;
            $id = sprintf('DP%02d', $count + 1);
        }

        return $id;
    }

    public function add_service_api($id = null, $data = null)
    {
        $query = false;
        foreach ($data as $service) {
            $new_id = $this->get_new_id_api();
            $content = [
                'id_detail_service_package' => $new_id,
                'id_package' => $id,
                'id_service_package' => $service,
                'created_at' => Time::now(),
                'updated_at' => Time::now(),
            ];
            $query = $this->db->table($this->table)->insert($content);
        }
        return $query;
    }

    public function update_service_api($id = null, $data = null)
    {
        $queryDel = $this->db->table($this->table)->delete(['id_package' => $id]);
        $queryIns = $this->add_service_api($id, $data);
        return $queryDel && $queryIns;
    }
}
