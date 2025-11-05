<?php

namespace App\Services;

use App\Models\MenuItemModel;

/**
 * Servicio para manejar la lógica de negocio relacionada con los ítems de menú.
 */
class MenuService
{
    private MenuItemModel $menuItemModel;

    public function __construct(MenuItemModel $menuItemModel = null)
    {
        $this->menuItemModel = $menuItemModel ?? new MenuItemModel();
    }

    /**
     * Devuelve sub-opciones activas por parent_id, opcionalmente filtradas por tipo de comida.
     *
     * @param int $parentId
     * @param string $mealType 'desayuno', 'comida', o 'ambos'
     * @return array
     */
    public function getActiveSubOptions(int $parentId, string $mealType = 'ambos'): array
    {
        $builder = $this->menuItemModel->where('activo', 1);

        if ($parentId === 0 || $parentId === null) {
            $builder->where('parent_id', null);
        } else {
            $builder->where('parent_id', $parentId);
        }

        // Aplicar filtro de tipo de comida si no es 'ambos'
        if ($mealType !== 'ambos') {
            $builder->groupStart()
                    ->where('tipo_comida', $mealType)
                    ->orWhere('tipo_comida', 'ambos') // Incluir siempre los que aplican a ambos
                    ->groupEnd();
        }

        return $builder->findAll();
    }

    /**
     * Devuelve todos los ítems de menú ordenados por parent_id
     *
     * @return array
     */
    public function getAllMenuItems(): array
    {
        return $this->menuItemModel->orderBy('parent_id', 'ASC')->findAll();
    }

    /**
     * Devuelve los ítems por IDs.
     *
     * @param array $ids
     * @return array
     */
    public function getItemsByIds(array $ids): array
    {
        if (empty($ids)) return [];
        return $this->menuItemModel->whereIn('id_item', $ids)->findAll();
    }

    /**
     * Devuelve un ítem por su ID.
     *
     * @param int $id
     * @return array|null
     */
    public function getById(int $id): ?array
    {
        return $this->menuItemModel->find($id);
    }

    /**
     * Devuelve el ítem raíz del menú (aquel sin padre).
     *
     * @return array|null
     */
    public function getRootItem(): ?array
    {
        return $this->menuItemModel->where('parent_id', null)->where('activo', 1)->first();
    }

    /**
     * Crea un nuevo ítem de menú y devuelve su ID o false si falla.
     *
     * @param array $data
     * @return int|false
     */
    public function createItem(array $data)
    {
        // Asegurar parent_id null cuando esté vacío
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        $insertId = $this->menuItemModel->insert($data, true);
        return $insertId ?: false;
    }

    /**
     * Actualiza un ítem por ID.
     *
     * @param int $id
     * @param array $data
     * @return bool
     */
    public function updateItem(int $id, array $data): bool
    {
        if (empty($data['parent_id'])) {
            $data['parent_id'] = null;
        }

        return (bool) $this->menuItemModel->update($id, $data);
    }

    /**
     * Elimina un ítem y todos sus descendientes en cascada.
     *
     * @param int $id
     * @return bool
     */
    public function deleteItem(int $id): bool
    {
        $db = \Config\Database::connect();
        $db->transStart();

        try {
            // 1. Eliminar todos los hijos y sus descendientes
            $this->deleteAllChildren($id);

            // 2. Eliminar el ítem principal
            $this->menuItemModel->delete($id);

            $db->transCommit();
            return true;
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Error en deleteItem (cascada): ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Dado un array de IDs, devuelve un array con esos IDs y todos sus ancestros.
     *
     * @param array $itemIds
     * @return array
     */
    public function getItemIdsWithAncestors(array $itemIds): array
    {
        if (empty($itemIds)) {
            return [];
        }

        // Usamos un array asociativo como un Set para evitar duplicados
        $allIds = array_flip($itemIds);

        // Hacemos una copia de los IDs para iterar, ya que modificaremos el Set
        $queue = $itemIds;

        while (!empty($queue)) {
            $currentItemId = array_shift($queue);

            // Hacemos una consulta para obtener solo el parent_id
            $item = $this->menuItemModel->select('parent_id')->find($currentItemId);

            if ($item && $item['parent_id']) {
                $parentId = $item['parent_id'];

                // Si el padre no está ya en nuestro Set, lo añadimos
                if (!isset($allIds[$parentId])) {
                    $allIds[$parentId] = count($allIds); // El valor no importa
                    $queue[] = $parentId; // Añadimos el padre a la cola para buscar a sus ancestros
                }
            }
        }

        return array_keys($allIds);
    }

    /**
     * Verifica si un ítem de menú tiene hijos activos.
     *
     * @param int $itemId
     * @return bool
     */
    public function hasActiveChildren(int $itemId): bool
    {
        $count = $this->menuItemModel
            ->where('parent_id', $itemId)
            ->where('activo', 1)
            ->countAllResults();

        return $count > 0;
    }

    /**
     * Devuelve un ítem con toda su jerarquía de hijos (pasos y opciones).
     *
     * @param int $itemId
     * @return array|null
     */
    public function getItemWithFullHierarchy(int $itemId): ?array
    {
        $mainItem = $this->getById($itemId);
        if (!$mainItem) {
            return null;
        }

        $mainItem['steps'] = $this->getActiveSubOptions($itemId);

        foreach ($mainItem['steps'] as &$step) {
            $step['options'] = $this->getActiveSubOptions($step['id_item']);
        }

        return $mainItem;
    }

    /**
     * Elimina recursivamente todos los hijos de un ítem de menú.
     *
     * @param int $parentId
     */
    public function deleteAllChildren(int $parentId)
    {
        $children = $this->menuItemModel->where('parent_id', $parentId)->findAll();
        foreach ($children as $child) {
            // Llamada recursiva para eliminar a los nietos primero
            $this->deleteAllChildren($child['id_item']);
            // Eliminar el hijo
            $this->menuItemModel->delete($child['id_item']);
        }
    }
}