<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMenuItems extends Migration
{
    public function up()
    {
        // Estructura de la tabla Menu_Items (Maestra Dinámica)
        $this->forge->addField([
            'id_item' => [
                'type'           => 'INT',
                'constraint'     => 5,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'parent_id' => [
                'type'       => 'INT',
                'constraint' => 5,
                'unsigned'   => true,
                'null'       => true, // NULL para ítems raíz
            ],
            'nombre_item' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
            ],
            'tipo_ui' => [ // Motor de Extensibilidad
                'type'       => 'ENUM',
                'constraint' => ['radio', 'checkbox', 'quantity'],
                'default'    => 'radio',
            ],
            'descripcion' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'precio_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'activo' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1, // Flag de activación
            ],
        ]);
        $this->forge->addKey('id_item', true);
        $this->forge->addKey('parent_id'); // Índice para consultas jerárquicas y AJAX
        $this->forge->createTable('menu_items');
    }

    public function down()
    {
        $this->forge->dropTable('menu_items');
    }
}
