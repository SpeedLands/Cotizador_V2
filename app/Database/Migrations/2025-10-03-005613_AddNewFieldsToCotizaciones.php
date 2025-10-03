<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNewFieldsToCotizaciones extends Migration
{
    public function up()
    {
        // Definición de los nuevos campos a agregar
        $fields = [
            'tipo_evento' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
                'after'      => 'num_invitados', // Opcional: para orden
            ],
            'nombre_empresa' => [
                'type'       => 'VARCHAR',
                'constraint' => '150',
                'null'       => true, // Es condicional
            ],
            'hora_inicio' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'hora_consumo' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'hora_finalizacion' => [
                'type' => 'TIME',
                'null' => false,
            ],
            'direccion_evento' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'mesa_mantel' => [
                'type'       => 'VARCHAR',
                'constraint' => '10',
                'null'       => false,
            ],
            'mesa_mantel_especificar' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true, // Es condicional
            ],
            'dificultad_montaje' => [
                'type' => 'TEXT',
                'null' => false,
            ],
            'como_nos_conocio' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
            ],
            'tipo_consumidores' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => false,
            ],
            'restricciones_alimenticias' => [
                'type'       => 'VARCHAR',
                'constraint' => '255',
                'null'       => true,
            ],
            'rango_presupuesto' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
        ];

        // Agregar todos los campos a la tabla 'cotizaciones'
        $this->forge->addColumn('cotizaciones', $fields);
    }

    public function down()
    {
        // Lógica para revertir la migración (eliminar las columnas)
        $this->forge->dropColumn('cotizaciones', [
            'tipo_evento', 'nombre_empresa', 'hora_inicio', 'hora_consumo', 
            'hora_finalizacion', 'direccion_evento', 'mesa_mantel', 
            'mesa_mantel_especificar', 'dificultad_montaje', 'como_nos_conocio', 
            'tipo_consumidores', 'restricciones_alimenticias', 'rango_presupuesto'
        ]);
    }
}