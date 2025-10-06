<?php

namespace App\Models;

use CodeIgniter\Model;

class QuotationDataTableModel extends Model
{
    protected $table = 'cotizaciones';
    protected $primaryKey = 'id_cotizacion';

    // Columnas permitidas para la ordenación (mapeo de DataTables a la DB)
    protected $column_order = [
        'id_cotizacion',
        'cliente_nombre',
        'fecha_evento',
        'total_estimado',
        'status',
        'created_at'
    ];

    // Columnas permitidas para la búsqueda global
    protected $column_search = [
        'id_cotizacion',
        'cliente_nombre',
        'status'
    ];

    // Orden por defecto
    protected $order = ['id_cotizacion' => 'DESC'];

    private function _get_datatables_query($searchValue)
    {
        $builder = $this->builder();

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
            // Mapeo seguro del índice a la columna
            if (isset($this->column_order[$columnIndex])) {
                $builder->orderBy($this->column_order[$columnIndex], $columnDir);
            }
        } else if ($this->order) {
            $order = $this->order;
            $builder->orderBy(key($order), $order[key($order)]);
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
        $builder = $this->builder();
        return $builder->countAllResults();
    }
}