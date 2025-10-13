<?php

namespace App\Controllers;

use App\Models\MenuItemDataTableModel;
use CodeIgniter\Controller;

class MenuItemDataTableController extends Controller
{
    public function getMenuItems()
    {
        if (!$this->request->isAJAX() || strtolower($this->request->getMethod()) !== 'post') {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

    $request = $this->request;
    $model = new MenuItemDataTableModel();

        $draw = $request->getPost('draw');
        $start = $request->getPost('start');
        $length = $request->getPost('length');
        $searchValue = $request->getPost('search')['value'] ?? '';
        $order = $request->getPost('order');

        if (!is_numeric($length) || $length > 100) {
            $length = 100;
        }

    $data = $model->getDatatables($searchValue, $order, $start, $length);
        $totalRecords = $model->countAll();
        $filteredRecords = $model->countFiltered($searchValue);

        $formattedData = [];
        foreach ($data as $row) {
            $formattedData[] = [
                'id_item' => $row['id_item'],
                'nombre_item' => esc($row['nombre_item']),
                'parent_name' => esc($row['parent_name'] ?? 'Categoría Raíz'),
                'tipo_ui' => esc($row['tipo_ui']),
                'precio_unitario' => number_format($row['precio_unitario'], 2, ',', '.'),
                'activo' => (int)$row['activo'],
                'actions' => $row['id_item']
            ];
        }

        $output = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $formattedData,
            'token' => csrf_hash()
        ];

        return $this->response->setJSON($output);
    }
}