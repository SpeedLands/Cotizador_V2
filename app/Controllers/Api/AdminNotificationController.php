<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class AdminNotificationController extends BaseController
{
    use ResponseTrait;

    /**
     * Lista todas las notificaciones de admin ordenadas por fecha.
     * Opcional: ?unread=1 para sólo no leídas, ?limit=50&offset=0 para paginar
     */
    public function index()
    {
        $model = new \App\Models\AdminNotificationModel();

        $limit = $this->request->getGet('limit');
        $offset = $this->request->getGet('offset');
        $unread = $this->request->getGet('unread');

        $builder = $model;
        if ($unread !== null && (string)$unread === '1') {
            $builder = $builder->where('is_read', 0);
        }

        $builder = $builder->orderBy('created_at', 'DESC');

        if ($limit !== null) {
            $limit = (int) $limit;
            $offset = (int) ($offset ?? 0);
            $data = $builder->findAll($limit, $offset);
        } else {
            $data = $builder->findAll();
        }

        return $this->respond($data);
    }

    /**
     * Marca una notificación como leída por ID
     * POST /api/v1/notifications/{id}/read
     */
    public function markAsRead($id = null)
    {
        if (!$id || !is_numeric($id)) {
            return $this->failValidationError('ID inválido.');
        }

        $model = new \App\Models\AdminNotificationModel();
        $record = $model->find($id);
        if (!$record) {
            return $this->failNotFound('Notificación no encontrada.');
        }

        $updated = $model->update($id, ['is_read' => 1]);
        if ($updated) {
            return $this->respondUpdated(['id' => $id, 'message' => 'Notificación marcada como leída.']);
        }

        return $this->failServerError('No se pudo marcar la notificación.');
    }

    /**
     * Marca varias notificaciones como leídas (bulk)
     * POST /api/v1/notifications/mark-read
     * Body: { "ids": [1,2,3] }
     */
    public function markReadBulk()
    {
        $payload = $this->request->getJSON(true) ?? [];
        $ids = $payload['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return $this->failValidationError('Se requieren IDs válidos.');
        }

        $model = new \App\Models\AdminNotificationModel();
        $sanitized = array_values(array_filter($ids, 'is_numeric'));

        if (empty($sanitized)) {
            return $this->failValidationError('No se recibieron IDs válidos.');
        }

        $count = 0;
        foreach ($sanitized as $id) {
            if ($model->update((int)$id, ['is_read' => 1])) {
                $count++;
            }
        }

        return $this->respond(['updated' => $count, 'requested' => count($sanitized)]);
    }
}
