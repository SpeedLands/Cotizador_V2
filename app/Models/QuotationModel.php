<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationModel extends Model
{
    protected $table            = 'cotizaciones';
    protected $primaryKey       = 'id_cotizacion';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    
    // Protección de Inserción
    protected $allowedFields = [
        'cliente_nombre', 'cliente_whatsapp', 'num_invitados',
        'fecha_evento', 'detalle_menu', 'notas_adicionales',
        'tipo_evento', 'nombre_empresa', 'hora_inicio', 'hora_consumo',
        'hora_finalizacion', 'direccion_evento', 'mesa_mantel',
        'mesa_mantel_especificar', 'dificultad_montaje', 'como_nos_conocio',
        'tipo_consumidores', 'restricciones_alimenticias', 'rango_presupuesto',
        'total_estimado', 'status', 'anticipo', 'resta',
        'download_token', 'modalidad_servicio'
    ];

    // Manejo de Tiempos
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Casting de Datos Complejos (JSON a Array)
    protected array $casts = [
        'detalle_menu' => 'json-array',
    ];

    /**
     * Cuenta las cotizaciones según su estado.
     */
    public function contarPorEstado(string $status): int
    {
        return $this->where('status', $status)->countAllResults();
    }

    /**
     * Obtiene los ingresos totales de las cotizaciones confirmadas en un mes específico.
     */
    public function ingresosConfirmadosPorMes(int $year, int $month): float
    {
        $resultado = $this->selectSum('total_estimado')
                          ->where('status', 'Confirmado')
                          ->where('YEAR(fecha_evento)', $year)
                          ->where('MONTH(fecha_evento)', $month)
                          ->get()
                          ->getRow();

        return (float)($resultado->total_estimado ?? 0);
    }

    /**
     * Obtiene las N cotizaciones más recientes.
     */
    public function getUltimasCotizaciones(int $limit = 5): array
    {
        // CRÍTICO: Usar 'created_at' en lugar de 'fecha_creacion'
        return $this->orderBy('created_at', 'DESC') 
                    ->limit($limit)
                    ->findAll();
    }

    /**
     * VERSIÓN OPTIMIZADA: Obtiene los ingresos totales confirmados para los últimos N meses.
     */
    public function getIngresosUltimosMeses(int $numeroDeMeses = 6): array
    {
        $datosGrafica = [
            'labels' => [],
            'data'   => [],
        ];
        $ingresosPorMes = [];

        // 1. Preparamos un array con los últimos N meses, inicializados en 0
        $formatter = new \IntlDateFormatter('es_ES', \IntlDateFormatter::NONE, \IntlDateFormatter::NONE, null, null, 'MMMM');
        for ($i = 0; $i < $numeroDeMeses; $i++) {
            $fecha = strtotime("-$i months");

            // Usar IntlDateFormatter para obtener el nombre del mes en español
            $mesNombre = $formatter->format($fecha);
            $mesAno = date('Y-m', $fecha);
            
            $datosGrafica['labels'][] = ucfirst($mesNombre);
            $ingresosPorMes[$mesAno] = 0;
        }

        // 2. Obtenemos los datos reales de la DB en UNA SOLA CONSULTA
        $fechaLimite = date('Y-m-01', strtotime("-" . ($numeroDeMeses - 1) . " months"));
        $resultados = $this->select("SUM(total_estimado) as total, DATE_FORMAT(fecha_evento, '%Y-%m') as mes_ano")
                           ->where('status', 'Confirmado')
                           ->where('fecha_evento >=', $fechaLimite)
                           ->groupBy("DATE_FORMAT(fecha_evento, '%Y-%m')")
                           ->get()
                           ->getResultArray();

        // 3. Llenamos nuestro array con los datos de la DB
        foreach ($resultados as $row) {
            if (isset($ingresosPorMes[$row['mes_ano']])) {
                $ingresosPorMes[$row['mes_ano']] = (float)$row['total'];
            }
        }

        // 4. Asignamos los datos al array final y los invertimos para el orden cronológico correcto
        $datosGrafica['data'] = array_values($ingresosPorMes);
        $datosGrafica['labels'] = array_reverse($datosGrafica['labels']);
        $datosGrafica['data'] = array_reverse($datosGrafica['data']);

        return $datosGrafica;
    }

    /**
     * Calcula los KPIs para la tasa de conversión.
     */
    public function getConversionRateKpi(): array
    {
        // 1. Contamos el total de cotizaciones que no estén canceladas
        $totalCotizaciones = $this->where('status !=', 'Cancelado')->countAllResults();

        // 2. Contamos las que se consideran una conversión exitosa
        $estadosExitosos = ['Confirmado', 'Pagado Parcial', 'Pagado Total'];
        $totalConfirmadas = $this->whereIn('status', $estadosExitosos)->countAllResults();

        // 3. Calculamos la tasa, evitando la división por cero
        $tasa = 0;
        if ($totalCotizaciones > 0) {
            $tasa = ($totalConfirmadas / $totalCotizaciones) * 100;
        }

        return [
            'total'       => $totalCotizaciones,
            'confirmadas' => $totalConfirmadas,
            'tasa'        => round($tasa, 2) // Redondeamos a 2 decimales
        ];
    }

    /**
     * Obtiene estadísticas agrupadas por el canal de origen (cómo supieron de nosotros).
     */
    public function getStatsPorCanalOrigen(): array
    {
        // CRÍTICO: Usar 'como_nos_conocio' en lugar de 'como_supiste'
        $query = $this->select('como_nos_conocio, COUNT(id_cotizacion) as total')
                      ->where('status !=', 'Cancelado')
                      ->groupBy('como_nos_conocio')
                      ->orderBy('total', 'DESC')
                      ->findAll();

        $stats = [
            'labels' => [],
            'data'   => [],
        ];

        foreach ($query as $row) {
            $label = empty($row['como_nos_conocio']) ? 'No especificado' : $row['como_nos_conocio'];
            $stats['labels'][] = $label;
            $stats['data'][] = (int)$row['total'];
        }

        return $stats;
    }

    /**
     * Obtiene estadísticas agrupadas por el tipo de evento.
     */
    public function getStatsPorTipoEvento(): array
    {
        $query = $this->select('tipo_evento, COUNT(id_cotizacion) as total')
                      ->where('status !=', 'Cancelado')
                      ->groupBy('tipo_evento')
                      ->orderBy('total', 'DESC')
                      ->findAll();

        $stats = [
            'labels' => [],
            'data'   => [],
        ];

        foreach ($query as $row) {
            $label = empty($row['tipo_evento']) ? 'No especificado' : $row['tipo_evento'];
            $stats['labels'][] = $label;
            $stats['data'][] = (int)$row['total'];
        }

        return $stats;
    }

    /**
     * Obtiene la distribución de los totales de cotizaciones por rangos de precios.
     */
    public function getQuoteTotalDistribution(): array
    {
        $rawSelect = "
            CASE
                WHEN total_estimado + 0 BETWEEN 0 AND 4999 THEN '0 - 4,999'
                WHEN total_estimado + 0 BETWEEN 5000 AND 9999 THEN '5,000 - 9,999'
                WHEN total_estimado + 0 BETWEEN 10000 AND 14999 THEN '10,000 - 14,999'
                WHEN total_estimado + 0 BETWEEN 15000 AND 19999 THEN '15,000 - 19,999'
                ELSE '20,000+'
            END as price_range,
            CASE
                WHEN total_estimado + 0 BETWEEN 0 AND 4999 THEN 1
                WHEN total_estimado + 0 BETWEEN 5000 AND 9999 THEN 2
                WHEN total_estimado + 0 BETWEEN 10000 AND 14999 THEN 3
                WHEN total_estimado + 0 BETWEEN 15000 AND 19999 THEN 4
                ELSE 5
            END as range_order,
            COUNT(id_cotizacion) as total
        ";

        $query = $this->select($rawSelect, false)
        ->where('status !=', 'Cancelado')
        ->groupBy('price_range, range_order')
        ->orderBy('range_order', 'ASC')
        ->findAll();

        $stats = [
            'labels' => [],
            'data'   => [],
        ];

        // Mapeo para el orden correcto
        $rangeOrder = [
            '0 - 4,999' => 0,
            '5,000 - 9,999' => 0,
            '10,000 - 14,999' => 0,
            '15,000 - 19,999' => 0,
            '20,000+' => 0,
        ];

        foreach ($query as $row) {
            if (isset($rangeOrder[$row['price_range']])) {
                $rangeOrder[$row['price_range']] = (int)$row['total'];
            }
        }

        $stats['labels'] = array_keys($rangeOrder);
        $stats['data'] = array_values($rangeOrder);

        return $stats;
    }
}