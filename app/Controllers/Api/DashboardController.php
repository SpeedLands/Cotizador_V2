<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\AdminDashboardService;
use CodeIgniter\API\ResponseTrait;

class DashboardController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $dashboardService = new AdminDashboardService();
        $data = $dashboardService->getDashboardData();

        // Devolvemos solo los datos relevantes para la API
        return $this->respond($data);
    }
}