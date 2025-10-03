<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuItemModel extends Model
{
    protected $table            = 'menu_items';
    protected $primaryKey       = 'id_item';
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    
    // Permitir la manipulación de todos los campos del menú
    protected $allowedFields = [
        'parent_id', 'nombre_item', 'tipo_ui', 'descripcion', 'precio_unitario', 'activo'
    ];
}