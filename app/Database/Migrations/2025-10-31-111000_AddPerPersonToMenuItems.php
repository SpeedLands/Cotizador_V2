<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPerPersonToMenuItems extends Migration
{
    public function up()
    {
        $this->forge->addColumn('menu_items', [
            'per_person' => [
                'type' => 'BOOLEAN',
                'default' => false,
                'null' => false,
                'after' => 'precio_unitario',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('menu_items', 'per_person');
    }
}