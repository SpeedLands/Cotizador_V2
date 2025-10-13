<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

class AdminController extends BaseController
{
    use ResponseTrait;

    /**
     * Devuelve los datos de detalle de una cotización (igual que AdminController::viewCotizacion)
     */
    public function viewCotizacion(int $id_cotizacion)
    {
        $viewService = service('quotationViewService');
        try {
            $data = $viewService->getDataForQuotationDetail($id_cotizacion);
            return $this->respond($data);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            return $this->failNotFound($e->getMessage());
        }
    }

    /**
     * Devuelve datos para editar una cotización (payload para precarga)
     */
    public function editCotizacion(int $id_cotizacion)
    {
        $quotationModel = new \App\Models\QuotationModel();
        $cotizacion = $quotationModel->find($id_cotizacion);

        if (!$cotizacion) {
            return $this->failNotFound('Cotización no encontrada.');
        }

        $data = [
            'cotizacion' => $cotizacion,
            'menuSeleccionadoJson' => json_encode($cotizacion['detalle_menu']),
            'isEditing' => true,
        ];

        return $this->respond($data);
    }

    /**
     * Actualiza una cotización (delegado a quotationService)
     */
    public function updateCotizacion()
    {
        $quotationService = service('quotationService');
        $validationRules = $quotationService->getValidationRules();

        $post = $this->request->getJSON(true) ?? $this->request->getPost();
        $cotizacionId = $post['id_cotizacion'] ?? null;

        if (!$cotizacionId) {
            return $this->failValidationError('id_cotizacion es requerido.');
        }

        $validationRules['id_cotizacion'] = 'required|is_natural_no_zero';

        if (!$this->validate($validationRules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $success = $quotationService->updateQuotation($cotizacionId, $post);

        if ($success) {
            return $this->respond(['message' => 'La cotización ha sido actualizada exitosamente.', 'id' => $cotizacionId]);
        }

        return $this->failServerError('Hubo un error al actualizar la cotización.');
    }

    /**
     * Actualiza el estado de una cotización (igual que AdminController::updateStatus)
     */
    public function updateStatus()
    {
        $data = $this->request->getJSON(true) ?? $this->request->getPost();
        $cotizacionId = $data['cotizacion_id'] ?? null;
        $newStatus = $data['status'] ?? null;

        $rules = [
            'cotizacion_id' => 'required|is_natural_no_zero',
            'status'        => 'required|in_list[pendiente,confirmado,cancelado,pagado,contactado,en_revision]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $quotationModel = new \App\Models\QuotationModel();
        $updateResult = $quotationModel->update($cotizacionId, ['status' => $newStatus]);

        if ($updateResult) {
            return $this->respond(['message' => 'El estado de la cotización ha sido actualizado.', 'id' => $cotizacionId]);
        }

        return $this->failServerError('No se pudo actualizar el estado de la cotización.');
    }
}
