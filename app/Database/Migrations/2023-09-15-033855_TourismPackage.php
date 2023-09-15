<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TourismPackage extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_homestay' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
            ],
            'price' => [
                'type' => 'INT',
                'default' => 0
            ],
            'capacity' => [
                'type' => 'INT',
                'default' => 0
            ],
            'cp' => [
                'type' => 'VARCHAR',
                'constraint'=> 13,
            ],
            'description' => [
                'type' => 'VARCHAR',
                'constraint'=> 255,
            ],
            'url' => [
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
        $this->forge->addPrimaryKey('id');
        $this->forge->addForeignKey('id_homestay', 'homestay', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('tourism_package');
        $this->db->enableForeignKeyChecks();
    }
    
    public function down()
    {
        $this->forge->dropTable('tourism_package');
    }
}
