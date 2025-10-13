<?php

namespace App\Controllers;

use App\Models\QuotationModel;
use CodeIgniter\API\ResponseTrait;

class CalendarController extends BaseController
{
    use ResponseTrait;

    /**
     * Muestra la vista principal del calendario.
     */
    public function index()
    {
        return view('admin/calendario/index');
    }

    /**
     * Endpoint AJAX para obtener los eventos para FullCalendar.
     */
    public function getEvents()
    {
        // 1. Obtener los parámetros de rango enviados por FullCalendar
        $start = $this->request->getVar('start');
        $end = $this->request->getVar('end');

        if (empty($start) || empty($end)) {
            return $this->failValidationError('Faltan parámetros de rango de fecha [start, end].');
        }

        // Usar el servicio centralizado
        $calendarService = service('calendarService');
        $eventsDB = $calendarService->getEventsForRange($start, $end);

        $calendarEvents = [];
        $statusColors = [
            'pendiente' => '#f59e0b',  // amber-500
            'confirmado' => '#10b981', // emerald-500
            'pagado' => '#3b82f6',     // blue-500
            'cancelado' => '#ef4444',  // red-500
            'contactado' => '#6366f1', // indigo-500
            'en_revision' => '#6b7280',// gray-500
        ];

        // 3. Mapear los resultados al formato JSON de FullCalendar
        // Si el servicio ya devolvió los eventos en el formato esperado, simplemente responder
        return $this->respond($eventsDB);
    }
}