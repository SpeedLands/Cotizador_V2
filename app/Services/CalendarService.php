<?php

namespace App\Services;

use App\Models\QuotationModel;

/**
 * Servicio para lógica del calendario (eventos basados en cotizaciones).
 */
class CalendarService
{
    private QuotationModel $quotationModel;

    public function __construct(QuotationModel $quotationModel = null)
    {
        $this->quotationModel = $quotationModel ?? new QuotationModel();
    }

    /**
     * Devuelve eventos en formato para FullCalendar en un rango dado.
     *
     * @param string $start
     * @param string $end
     * @return array
     */
    public function getEventsForRange(string $start, string $end): array
    {
        $eventsDB = $this->quotationModel
            ->where('fecha_evento >=', $start)
            ->where('fecha_evento <=', $end)
            ->findAll();

        $calendarEvents = [];
        $statusColors = [
            'pendiente' => '#f59e0b',
            'confirmado' => '#10b981',
            'pagado' => '#3b82f6',
            'cancelado' => '#ef4444',
            'contactado' => '#6366f1',
            'en_revision' => '#6b7280',
        ];

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

        return $calendarEvents;
    }
}