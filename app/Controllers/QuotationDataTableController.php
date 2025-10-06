<?php

namespace App\Controllers;

use App\Models\QuotationDataTableModel;
use CodeIgniter\Controller;

class QuotationDataTableController extends Controller
{
    public function getQuotations()
    {
        // Validación de la solicitud
        if (!$this->request->isAJAX() || strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $request = $this->request;
        $model = new QuotationDataTableModel();

        // Parámetros de DataTables
        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'] ?? '';
        $order = $request->getPost('order');

        // Validación de seguridad para 'length'
        if (!is_numeric($length) || $length > 100) {
            $length = 100; // Forzar un límite máximo seguro
        }

        // Obtener datos del modelo
        $data = $model->getDatatables($searchValue, $order, $start, $length);
        $totalRecords = $model->countAll();
        $filteredRecords = $model->countFiltered($searchValue);

        $formattedData = [];
        foreach ($data as $row) {
            // Aquí formateamos los datos para la vista, pero el renderizado de HTML se hará en el frontend
            $formattedData[] = [
                'id_cotizacion' => $row['id_cotizacion'],
                'cliente_nombre' => esc($row['cliente_nombre']),
                'fecha_evento' => date('d/m/Y', strtotime($row['fecha_evento'])),
                'total_estimado' => number_format($row['total_estimado'], 2, ',', '.'),
                'status' => strtolower($row['status']), // Se envía el valor crudo para el renderizado en JS
                'created_at' => date('d/m/Y H:i', strtotime($row['created_at'])),
                'actions' => $row['id_cotizacion'] // Solo pasamos el ID para construir los botones en JS
            ];
        }

        $output = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData,
            'token' => csrf_hash() // Devolver el nuevo token CSRF
        ];

        return $this->response->setJSON($output);
    }
}