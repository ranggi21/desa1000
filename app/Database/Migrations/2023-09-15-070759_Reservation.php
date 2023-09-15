<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Reservation extends Migration
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
            'id_package' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'id_reservation_status' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
            ],
            'request_date' => [
                'type' => 'DATE',
                'null' => false,
            ],
            'deposit' => [
                'type' => 'INT',
                'default' => 0
            ],
            'total_price' => [
                'type' => 'INT',
                'default' => 0
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
        $this->forge->addForeignKey('id_package', 'tourism_package', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('id_reservation_status', 'reservation_status', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('reservation');
        $this->db->enableForeignKeyChecks();
    }
    
    public function down()
    {
        $this->forge->dropTable('reservation');
    }
}
