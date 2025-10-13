<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;
use App\Models\QuotationModel;
use App\Services\QuotationService;
use App\Services\QuotationViewService;

class QuotationController extends BaseController
{
    use ResponseTrait;

    // ... otros métodos ...

    public function index()
    {
        $model = new QuotationModel();
        $data = $model->orderBy('created_at', 'DESC')->findAll();
        return $this->respond($data);
    }

    public function show($id = null)
    {
        $viewService = service('quotationViewService') ?? new QuotationViewService();
        try {
            $data = $viewService->getDataForQuotationDetail($id);
            return $this->respond($data);
        } catch (\CodeIgniter\Exceptions\PageNotFoundException $e) {
            return $this->failNotFound($e->getMessage());
        }
    }

    public function create()
    {
        $quotationService = service('quotationService');
        $rules = $quotationService->getValidationRules();

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        // CRÍTICO: Asignar el user_id del usuario autenticado
        $data['user_id'] = $this->request->user_id; 

        $data = $this->request->getJSON(true);
        $id = $quotationService->createQuotation($data);

        if ($id) {
            return $this->respondCreated(['id' => $id], 'Cotización creada');
        }

        return $this->failServerError('No se pudo crear la cotización.');
    }

    public function update($id = null)
    {
        $quotationService = service('quotationService');
        $rules = $quotationService->getValidationRules();

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $data = $this->request->getJSON(true);
        $success = $quotationService->updateQuotation($id, $data);

        if ($success) {
            return $this->respondUpdated(['id' => $id], 'Cotización actualizada');
        }

        return $this->failServerError('No se pudo actualizar la cotización.');
    }

    public function delete($id = null)
    {
        $model = new QuotationModel();
        if ($model->find($id)) {
            $model->delete($id);
            return $this->respondDeleted(['id' => $id], 'Cotización eliminada');
        }
        return $this->failNotFound('No se encontró la cotización con ID ' . $id);
    }

    public function updateStatus($id = null)
    {
        $rules = [
            'status' => 'required|in_list[pendiente,confirmado,cancelado,pagado,contactado,en_revision]',
        ];

        if (!$this->validate($rules)) {
            return $this->failValidationErrors($this->validator->getErrors());
        }

        $model = new QuotationModel();
        $newStatus = $this->request->getJSON(true)['status'];

        if ($model->update($id, ['status' => $newStatus])) {
            return $this->respond(['message' => 'Estado actualizado'], 200);
        }

        return $this->failServerError('No se pudo actualizar el estado.');
    }
}