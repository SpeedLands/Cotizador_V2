<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdminNotifications extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'quotation_id' => [
                'type'       => 'INT',
                'constraint' => 9,
                'unsigned'   => true,
                'null'       => false,
                // Opcional: Añadir clave foránea si la tabla cotizaciones ya existe
                // 'foreign_key' => 'id_cotizacion',
                // 'references'  => 'cotizaciones',
            ],
            'message' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => false,
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0, // 0 = No leído, 1 = Leído
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('quotation_id'); // Índice para búsquedas rápidas
        $this->forge->createTable('admin_notifications');
    }

    public function down()
    {
        $this->forge->dropTable('admin_notifications');
    }
}