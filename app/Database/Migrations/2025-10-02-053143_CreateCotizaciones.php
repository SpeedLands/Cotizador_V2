<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCotizaciones extends Migration
{
    // Estructura de la tabla Cotizaciones (Transaccional)
    public function up()
    {
        $this->forge->addField([
            'id_cotizacion' => [
                'type'           => 'INT',
                'constraint'     => 9,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'cliente_nombre' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'cliente_whatsapp' => [
                'type'       => 'VARCHAR',
                'constraint' => '20',
            ],
            'num_invitados' => [
                'type'       => 'INT',
                'constraint' => 5,
            ],
            'fecha_evento' => [
                'type' => 'DATETIME',
            ],
            'detalle_menu' => [ // JSON para inmutabilidad
                'type' => 'JSON',
            ],
            'notas_adicionales' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addKey('id_cotizacion', true);
        $this->forge->createTable('cotizaciones');
    }

    public function down()
    {
        $this->forge->dropTable('cotizaciones');
    }
}
