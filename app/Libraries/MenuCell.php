<?php

namespace App\Libraries;

use CodeIgniter\View\Cells\Cell;
use App\Models\MenuItemModel;

class MenuCell extends Cell
{
    // Función para renderizar los ítems de nivel superior (parent_id = NULL)
    public function renderRootItems()
    {
        $menuModel = new MenuItemModel();
        // Obtener ítems raíz activos
        $rootItems = $menuModel->where('parent_id', null)
                               ->where('activo', 1)
                               ->findAll();

        // Pasar los datos a una vista parcial para el renderizado
        return view('quotation/partials/_menu_root', ['rootItems' => $rootItems]);
    }
}