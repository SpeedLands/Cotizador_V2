<?php

namespace App\Models;

use CodeIgniter\Model;

class AdminNotificationModel extends Model
{
    protected $table            = 'admin_notifications';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    
    // Campos para la notificación interna
    protected $allowedFields = [
        'quotation_id', 'message', 'is_read', 'created_at'
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = ''; // No necesitamos updated_at
}