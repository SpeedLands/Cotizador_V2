<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddAnticipoToQuotations extends Migration
{
    public function up()
    {
        $fields = [
            'anticipo' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'default' => 0.00,
                'after' => 'total_estimado',
            ],
            'resta' => [
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'null' => true,
                'default' => 0.00,
                'after' => 'anticipo',
            ],
        ];
        $this->forge->addColumn('cotizaciones', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('cotizaciones', ['anticipo', 'resta']);
    }
}
