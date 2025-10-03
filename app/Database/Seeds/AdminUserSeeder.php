<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\AdminUserModel;

class AdminUserSeeder extends Seeder
{
    public function run()
    {
        $userModel = new AdminUserModel();
        
        // Datos del usuario de prueba
        $data = [
            'email'    => 'admin@gmail.com',
            'password' => 'admin123', // El modelo lo hasheará automáticamente
        ];

        $userModel->insert($data);
    }
}
