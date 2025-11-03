<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;


class AddModalidadServicioToQuotations extends Migration
{
    public function up()
    {
        $fields = [
            'modalidad_servicio' => [
                'type' => 'VARCHAR',
                'constraint' => '100',
                'null' => false,
                'after' => 'direccion_evento', // Colocar después de la dirección
            ],
        ];
        $this->forge->addColumn('cotizaciones', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('cotizaciones', 'modalidad_servicio');
    }
}