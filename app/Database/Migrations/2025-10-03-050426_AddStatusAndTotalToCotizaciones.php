<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddStatusAndTotalToCotizaciones extends Migration
{
    public function up()
    {
        $fields = [
            'total_estimado' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
                'null'       => false,
            ],
            'status' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'Pendiente',
                'null'       => false,
            ],
        ];
        $this->forge->addColumn('cotizaciones', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('cotizaciones', ['total_estimado', 'status']);
    }
}
