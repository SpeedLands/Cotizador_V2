<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoComidaToMenuItems extends Migration
{
    public function up()
    {
        $fields = [
            'tipo_comida' => [
                'type' => 'VARCHAR',
                'constraint' => '50',
                'null' => true,
                'after' => 'activo',
            ],
        ];
        $this->forge->addColumn('menu_items', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('menu_items', 'tipo_comida');
    }
}