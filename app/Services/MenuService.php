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
     * Devuelve sub-opciones activas por parent_id
     *
     * @param int $parentId
     * @return array
     */
    public function getActiveSubOptions(int $parentId): array
    {
        // Soportar parentId == 0 o null como raíz (parent_id IS NULL)
        if ($parentId === 0 || $parentId === null) {
            return $this->menuItemModel->where('parent_id', null)
                ->where('activo', 1)->findAll();
        }

        return $this->menuItemModel->where('parent_id', $parentId)
            ->where('activo', 1)->findAll();
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
     * Elimina un ítem por ID, devolviendo true si se elimina.
     *
     * @param int $id
     * @return bool
     */
    public function deleteItem(int $id): bool
    {
        // Evitar eliminación si tiene hijos
        $childCount = $this->menuItemModel->where('parent_id', $id)->countAllResults();
        if ($childCount > 0) {
            return false;
        }

        return (bool) $this->menuItemModel->delete($id);
    }
}
