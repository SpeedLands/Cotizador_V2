<?php

namespace App\Services;

use App\Models\QuotationModel;

/**
 * Servicio para manejar la lógica de negocio del Dashboard de Administración.
 */
class AdminDashboardService
{
    private QuotationModel $quotationModel;

    public function __construct()
    {
        $this->quotationModel = new QuotationModel();
    }

    /**
     * Prepara todos los datos necesarios para la vista del dashboard.
     *
     * @return array
     */
    public function getDashboardData(): array
    {
        $baseURL = base_url('panel'); // Usar la nueva ruta base del panel

        $navLinks = [
            'Dashboard' => ['url' => $baseURL . '/dashboard', 'active' => true],
            'Cotizaciones' => ['url' => '#', 'active' => false], // Actualizar si hay una vista de lista
            'Calendario' => ['url' => '#', 'active' => false],
            'Servicios' => ['url' => '#', 'active' => false],
        ];

        $uiLabels = [
            'social' => 'Evento Social',
            'empresarial' => 'Evento Empresarial',
            'otro' => 'Otro',
            'recomendacion' => 'Recomendación',
            'redes' => 'Redes Sociales',
            'restaurante' => 'Por el Restaurante',
            'hombres' => 'Hombres',
            'mujeres' => 'Mujeres',
            'ninos' => 'Niños',
            'mixto' => 'Mixto',
            'pendiente' => 'Pendiente',
            'confirmado' => 'Confirmado',
            'cancelado' => 'Cancelado',
            'pagado' => 'Pagado',
            'contactado' => 'Contactado',
            'en_revision' => 'En Revisión',
            'No especificado' => 'No especificado',
        ];

        return [
            'currentPage' => 'Dashboard',
            'baseURL' => $baseURL,
            'navLinks' => $navLinks,
            'isLoggedIn' => session()->get('isLoggedIn') ?? false,

            // --- DATOS DEL DASHBOARD (KPIs) ---
            'pendientes' => $this->quotationModel->contarPorEstado('Pendiente'),
            'confirmadas_mes' => $this->contarConfirmadasMesActual(),
            'ingresos_mes' => $this->quotationModel->ingresosConfirmadosPorMes(date('Y'), date('m')),
            'kpi_conversion' => $this->quotationModel->getConversionRateKpi(),

            // --- DATOS DE TABLAS ---
            'ultimas_cotizaciones' => $this->quotationModel->getUltimasCotizaciones(5),

            // --- DATOS DE GRÁFICAS (JSON) ---
            'grafica_ingresos' => $this->quotationModel->getIngresosUltimosMeses(6),
            'grafica_ingresos_json' => json_encode($this->quotationModel->getIngresosUltimosMeses(6)),
            'uiLabels' => $uiLabels,

            'stats_canal_origen' => $this->quotationModel->getStatsPorCanalOrigen(),
            'stats_tipo_evento' => $this->quotationModel->getStatsPorTipoEvento(),
        ];
    }

    /**
     * Función auxiliar para contar cotizaciones confirmadas en el mes actual.
     *
     * @return int
     */
    private function contarConfirmadasMesActual(): int
    {
        return $this->quotationModel->where('status', 'Confirmado')
            ->where('YEAR(created_at)', date('Y'))
            ->where('MONTH(created_at)', date('m'))
            ->countAllResults();
    }
}
