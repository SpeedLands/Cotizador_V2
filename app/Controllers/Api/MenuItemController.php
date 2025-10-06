<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MenuItemModel;
use CodeIgniter\API\ResponseTrait;

class MenuItemController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $model = new MenuItemModel();
        // Devolver todos los ítems para que Flutter construya la jerarquía
        $data = $model->orderBy('parent_id', 'ASC')->orderBy('orden', 'ASC')->findAll();
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $model = new MenuItemModel();
        $data = $model->find($id);
        if ($data) {
            return $this->respond($data);
        }
        return $this->failNotFound('No se encontró el servicio con ID ' . $id);
    }

    // Los métodos create, update y delete serían muy similares a los de QuotationController
    // y reutilizarían la lógica de los métodos storeService, updateService y deleteService
    // del AdminController, pero devolviendo respuestas JSON.
}