<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\CalendarService;
use CodeIgniter\API\ResponseTrait;

class CalendarController extends BaseController
{
    use ResponseTrait;

    public function getEvents()
    {
        // Obtenemos los parámetros directamente de la petición actual
        $start = $this->request->getVar('start');
        $end = $this->request->getVar('end');

        if (empty($start) || empty($end)) {
            return $this->failValidationError('Faltan parámetros de rango de fecha [start, end].');
        }

    // Usamos el servicio para obtener los datos
    $calendarService = service('calendarService');
    $events = $calendarService->getEventsForRange($start, $end);

        return $this->respond($events);
    }
}