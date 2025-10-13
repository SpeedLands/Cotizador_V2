<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MenuItemModel;
use CodeIgniter\API\ResponseTrait;
use App\Services\MenuService;

class MenuItemController extends BaseController
{
    use ResponseTrait;

    public function index()
    {
        $menuService = service('menuService');
        $data = $menuService->getAllMenuItems();
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

    /**
     * Crea un nuevo item de menú (API)
     * POST /api/v1/menu-items
     */
    public function create()
    {
        $rules = [
            'nombre_item' => 'required|max_length[255]',
            'tipo_ui' => 'required|in_list[nav_group,checkbox,radio,quantity]',
            'parent_id' => 'permit_empty|is_natural',
            'precio_unitario' => 'permit_empty|decimal',
            'activo' => 'required|in_list[0,1]',
        ];

        // En APIs esperamos JSON
        $data = $this->request->getJSON(true) ?? [];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $menuService = service('menuService');
        $id = $menuService->createItem($data);

        if ($id) {
            return $this->respondCreated(['id' => $id, 'message' => 'Servicio añadido exitosamente.']);
        }

        return $this->failServerError('No se pudo guardar el servicio.');
    }

    /**
     * Actualiza un item de menú (API)
     * PUT/PATCH /api/v1/menu-items/{id}
     */
    public function update($id = null)
    {
        $rules = [
            'id_item' => 'required|is_natural_no_zero',
            'nombre_item' => 'required|max_length[255]',
            'tipo_ui' => 'required|in_list[nav_group,checkbox,radio,quantity]',
            'parent_id' => 'permit_empty|is_natural',
            'precio_unitario' => 'permit_empty|decimal',
            'activo' => 'required|in_list[0,1]',
        ];

        $data = $this->request->getJSON(true) ?? [];
        // Asegurar que el ID venga en el payload o en la URL
        $data['id_item'] = $data['id_item'] ?? $id;

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $menuService = service('menuService');

        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        if ($data['parent_id'] == $id) {
            return $this->fail('Un servicio no puede ser su propia categoría padre.', 400);
        }

        if ($menuService->updateItem((int)$id, $data)) {
            return $this->respondUpdated(['id' => $id, 'message' => 'Servicio actualizado exitosamente.']);
        }

        return $this->failServerError('No se pudo actualizar el servicio.');
    }

    /**
     * Elimina un item de menú (API)
     * DELETE /api/v1/menu-items/{id}
     */
    public function delete($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->failValidationError('ID inválido.');
        }

        $menuService = service('menuService');

        if (! $menuService->deleteItem((int)$id)) {
            return $this->failServerError('No se puede eliminar una categoría que contiene sub-servicios o ocurrió un error.');
        }

        return $this->respondDeleted(['id' => $id, 'message' => 'Servicio eliminado exitosamente.']);
    }
}