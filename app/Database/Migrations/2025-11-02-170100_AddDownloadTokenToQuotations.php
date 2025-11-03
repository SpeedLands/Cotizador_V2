<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDownloadTokenToQuotations extends Migration
{
    public function up()
    {
        $this->forge->addColumn('cotizaciones', [
            'download_token' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'unique' => true,
                'after' => 'status',
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('cotizaciones', 'download_token');
    }
}