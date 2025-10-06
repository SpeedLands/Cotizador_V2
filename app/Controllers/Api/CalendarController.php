<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Controllers\CalendarController as WebCalendarController;
use CodeIgniter\API\ResponseTrait;

class CalendarController extends BaseController
{
    use ResponseTrait;

    // Reutilizamos el controlador web existente que ya devuelve JSON
    public function getEvents()
    {
        $webController = new WebCalendarController();
        return $webController->getEvents();
    }
}