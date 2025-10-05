<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Rutas de la API (Autenticación)
$routes->group('api', function($routes) {
    $routes->post('login', 'AuthController::login');
    $routes->post('token/refresh', 'AuthController::refresh');
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
$routes->get('admin/dashboard', 'AdminController::dashboard', ['as' => 'admin.dashboard']); // Dashboard
$routes->get('admin/logout', 'AdminController::logout', ['as' => 'admin.logout']);

$routes->get('admin/cotizaciones/ver/(:num)', 'AdminController::viewCotizacion/$1', ['as' => 'admin.cotizaciones.view']);

$routes->get('admin/cotizaciones/editar/(:num)', 'AdminController::editCotizacion/$1', ['as' => 'admin.cotizaciones.edit']);
$routes->post('admin/cotizaciones/actualizar', 'AdminController::updateCotizacion', ['as' => 'admin.cotizaciones.update']);