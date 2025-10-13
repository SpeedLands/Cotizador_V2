<?php

namespace Config;

use CodeIgniter\Config\BaseService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    /*
     * public static function example($getShared = true)
     * {
     *     if ($getShared) {
     *         return static::getSharedInstance('example');
     *     }
     *
     *     return new \CodeIgniter\Example();
     * }
     */
    public static function quotationService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('quotationService');
        }

        return new \App\Services\QuotationService(
            new \App\Models\QuotationModel(),
            new \App\Models\AdminNotificationModel(),
            new \App\Models\MenuItemModel()
        );
    }

    public static function calendarService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('calendarService');
        }

        return new \App\Services\CalendarService(
            new \App\Models\QuotationModel()
        );
    }

    public static function menuService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('menuService');
        }

        return new \App\Services\MenuService(
            new \App\Models\MenuItemModel()
        );
    }

    public static function quotationViewService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('quotationViewService');
        }

        return new \App\Services\QuotationViewService(
            new \App\Models\QuotationModel(),
            new \App\Models\MenuItemModel()
        );
    }

    public static function adminDashboardService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('adminDashboardService');
        }

        return new \App\Services\AdminDashboardService();
    }

    public static function authService($getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('authService');
        }

        return new \App\Services\AuthService();
    }
}

// Service factories de aplicación
if (! function_exists('quotationService')) {
    /**
     * Helper global que devuelve la instancia de QuotationService vía service('quotationService')
     * (opcional, para compatibilidad). Normalmente se usará service('quotationService').
     */
    function quotationService()
    {
        return service('quotationService');
    }
}

