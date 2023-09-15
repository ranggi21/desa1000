<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class HomeStay extends Migration
{
    public function up()
    {
        $fields = [
            'id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_user' => [
                'type' => 'int',
                'constraint' => 11,
                'unsigned' => true
            ],
            'name' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false
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
        $this->forge->addForeignKey('id_user', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('homestay');
        $this->db->enableForeignKeyChecks();
    }
    
    public function down()
    {
        $this->forge->dropTable('homestay');
    }
}
