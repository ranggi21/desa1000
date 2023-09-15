<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DetailHomeStayUnitFacility extends Migration
{
    public function up()
    {
        $fields = [
            'id_homestay_unit' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'id_homestay_unit_facility' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint'=> 255,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
            ],
        ];
    
        $this->db->disableForeignKeyChecks();
        $this->forge->addField($fields);
        $this->forge->addForeignKey('id_homestay_unit', 'homestay_unit', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_homestay_unit_facility', 'id', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('detail_homestay_unit_facility');
        $this->db->enableForeignKeyChecks();
    }

    public function down()
    {
        $this->forge->dropTable('detail_homestay_unit_facility');
    }
}
