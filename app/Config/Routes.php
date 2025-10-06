<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rutas de la API v1 (Autenticación)
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function($routes) {
    $routes->post('login', 'ApiAuthController::login');
    $routes->post('token/refresh', 'ApiAuthController::refresh');
    $routes->post('logout', 'ApiAuthController::logout'); // Endpoint para revocar el refresh token
});

// --- Rutas Públicas de la API v1 (Sin autenticación) ---
$routes->post('api/v1/public/quotations', 'Api\PublicController::createQuotation');
$routes->post('api/v1/public/quotations/history', 'Api\PublicController::getHistory');
 
// --- Rutas de la API v1 para la App Móvil (Protegidas por JWT) ---
$routes->group('api/v1', ['namespace' => 'App\Controllers\Api', 'filter' => 'jwtAuth'], function ($routes) {
    // Dashboard
    $routes->get('dashboard', 'DashboardController::index');
    // Calendario
    $routes->get('calendar/events', 'CalendarController::getEvents');
    // Cotizaciones (Recurso RESTful)
    $routes->resource('quotations', ['controller' => 'QuotationController']);
    $routes->post('quotations/status/(:num)', 'QuotationController::updateStatus/$1');
    // Servicios/Menu (Recurso RESTful)
    $routes->resource('services', ['controller' => 'MenuItemController', 'only' => ['index', 'show']]);
});

// Rutas Públicas (Formulario de Cotización)
$routes->get('/', 'QuotationController::index');
$routes->post('cotizacion/submit', 'QuotationController::submitQuote');
$routes->post('cotizacion/ajax/suboptions', 'QuotationController::loadSubOptionsAjax');
$routes->post('cotizacion/ajax/calculate', 'QuotationController::calculateQuoteAjax');
$routes->get('cotizacion/confirmacion/(:num)', 'QuotationController::confirmation/$1');

// Rutas de Administración (Login y Dashboard)
$routes->get('admin', 'AdminController::login', ['as' => 'admin.login']); // Página de Login
$routes->post('admin/auth', 'AdminController::authenticate'); // Endpoint de autenticación
$routes->get('admin/logout', 'AdminController::logout', ['as' => 'admin.logout']);

// Grupo de rutas del panel de administración (protegidas)
$routes->group('panel', ['filter' => 'adminAuth'], function($routes) {
    $routes->get('servicios', 'AdminController::listServices', ['as' => 'panel.servicios.index']); // Vista de la tabla de servicios
    $routes->get('servicios/crear', 'AdminController::createService', ['as' => 'panel.servicios.crear']); // Vista del formulario de creación
    $routes->get('servicios/editar/(:num)', 'AdminController::editService/$1', ['as' => 'panel.servicios.editar']); // Vista del formulario de edición
    $routes->post('servicios/actualizar', 'AdminController::updateService', ['as' => 'panel.servicios.actualizar']); // Endpoint para actualizar
    $routes->post('servicios/eliminar', 'AdminController::deleteService', ['as' => 'panel.servicios.eliminar']); // Endpoint para eliminar
    $routes->post('servicios/guardar', 'AdminController::storeService', ['as' => 'panel.servicios.guardar']); // Endpoint para guardar
    $routes->post('servicios/datatable', 'MenuItemDataTableController::getMenuItems', ['as' => 'panel.servicios.datatable']); // Endpoint AJAX
    $routes->get('calendario', 'CalendarController::index', ['as' => 'panel.calendario.index']); // Vista del calendario
    $routes->get('calendario/eventos', 'CalendarController::getEvents', ['as' => 'panel.calendario.eventos']); // Endpoint AJAX
    $routes->get('cotizaciones', 'AdminController::listQuotations', ['as' => 'panel.cotizaciones.index']); // Vista de la tabla
    $routes->post('cotizaciones/datatable', 'QuotationDataTableController::getQuotations', ['as' => 'panel.cotizaciones.datatable']); // Endpoint AJAX
    $routes->get('dashboard', 'AdminController::dashboard', ['as' => 'panel.dashboard']);
    $routes->get('cotizaciones/ver/(:num)', 'AdminController::viewCotizacion/$1', ['as' => 'panel.cotizaciones.view']);
    $routes->get('cotizaciones/editar/(:num)', 'AdminController::editCotizacion/$1', ['as' => 'panel.cotizaciones.edit']);
    $routes->post('cotizaciones/actualizar', 'AdminController::updateCotizacion', ['as' => 'panel.cotizaciones.update']);
    $routes->post('cotizaciones/actualizar-estado', 'AdminController::updateStatus', ['as' => 'panel.cotizaciones.updateStatus']); // <-- NUEVA RUTA
});