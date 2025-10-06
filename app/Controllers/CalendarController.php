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

        $quotationModel = new QuotationModel();

        // 2. Consultar solo los eventos dentro del rango visible
        $eventsDB = $quotationModel
            ->where('fecha_evento >=', $start)
            ->where('fecha_evento <=', $end)
            ->findAll();

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
        foreach ($eventsDB as $event) {
            $calendarEvents[] = [
                'id' => $event['id_cotizacion'],
                'title' => 'Evento: ' . $event['cliente_nombre'],
                'start' => $event['fecha_evento'],
                'allDay' => true,
                'color' => $statusColors[strtolower($event['status'])] ?? '#6b7280',
                'url' => site_url(route_to('panel.cotizaciones.view', $event['id_cotizacion'])),
            ];
        }

        // 4. Devolver la respuesta JSON
        return $this->respond($calendarEvents);
    }
}