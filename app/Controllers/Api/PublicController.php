<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Services\QuotationService;
use CodeIgniter\API\ResponseTrait;

class PublicController extends BaseController
{
    use ResponseTrait;

    /**
     * Endpoint público para crear una nueva cotización.
     * POST /api/v1/public/quotations
     */
    public function createQuotation()
    {
        $quotationService = new QuotationService();
        $rules = $quotationService->getValidationRules();

        // La API espera datos JSON
        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $id = $quotationService->createQuotation($data);

        if ($id) {
            return $this->respondCreated(['id' => $id, 'message' => 'Cotización creada exitosamente.'], 'Cotización creada');
        }

        return $this->failServerError('No se pudo crear la cotización.');
    }

    /**
     * Endpoint público para obtener un historial de cotizaciones basado en una lista de IDs.
     * POST /api/v1/public/quotations/history
     */
    public function getHistory()
    {
        $data = $this->request->getJSON(true);
        $ids = $data['ids'] ?? [];

        if (empty($ids) || !is_array($ids)) {
            return $this->respond([]); // Devolver un array vacío si no se envían IDs
        }

        // Sanitizar los IDs para asegurarse de que son números
        $sanitizedIds = array_filter($ids, 'is_numeric');

        if (empty($sanitizedIds)) {
            return $this->respond([]);
        }

        $model = new \App\Models\QuotationModel();
        $quotations = $model->whereIn('id_cotizacion', $sanitizedIds)->orderBy('created_at', 'DESC')->findAll();

        return $this->respond($quotations);
    }
}