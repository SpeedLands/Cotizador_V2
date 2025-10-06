<?php

namespace App\Models;

use CodeIgniter\Model;

class MenuItemDataTableModel extends Model
{
    protected $table = 'menu_items';
    protected $primaryKey = 'id_item';

    // Columnas permitidas para la ordenación
    protected $column_order = [
        'mi.id_item',
        'mi.nombre_item',
        'parent.nombre_item',
        'mi.tipo_ui',
        'mi.precio_unitario',
        'mi.activo'
    ];

    // Columnas permitidas para la búsqueda
    protected $column_search = [
        'mi.nombre_item',
        'parent.nombre_item',
        'mi.tipo_ui'
    ];

    // Orden por defecto para mostrar la jerarquía
    protected $order = ['mi.parent_id' => 'ASC', 'mi.orden' => 'ASC'];

    private function _get_datatables_query($searchValue)
    {
        $builder = $this->db->table('menu_items as mi');
        // Hacemos un LEFT JOIN para obtener el nombre del padre
        $builder->select('mi.id_item, mi.nombre_item, mi.tipo_ui, mi.precio_unitario, mi.activo, parent.nombre_item as parent_name');
        $builder->join('menu_items as parent', 'parent.id_item = mi.parent_id', 'left');

        // Lógica de búsqueda global
        if ($searchValue) {
            $builder->groupStart();
            foreach ($this->column_search as $i => $item) {
                if ($i === 0) {
                    $builder->like($item, $searchValue);
                } else {
                    $builder->orLike($item, $searchValue);
                }
            }
            $builder->groupEnd();
        }

        return $builder;
    }

    public function getDatatables($searchValue, $order, $start, $length)
    {
        $builder = $this->_get_datatables_query($searchValue);

        // Lógica de ordenación
        if ($order) {
            $columnIndex = $order[0]['column'];
            $columnDir = $order[0]['dir'];
            if (isset($this->column_order[$columnIndex])) {
                $builder->orderBy($this->column_order[$columnIndex], $columnDir);
            }
        } else if ($this->order) {
            $defaultOrder = $this->order;
            foreach ($defaultOrder as $col => $dir) {
                $builder->orderBy($col, $dir);
            }
        }

        // Paginación
        if ($length != -1) {
            $builder->limit($length, $start);
        }

        return $builder->get()->getResultArray();
    }

    public function countFiltered($searchValue)
    {
        $builder = $this->_get_datatables_query($searchValue);
        return $builder->countAllResults();
    }

    public function countAll()
    {
        $builder = $this->db->table($this->table);
        return $builder->countAllResults();
    }
}