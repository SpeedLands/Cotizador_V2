<?php

namespace App\Libraries;

// No se necesita el 'require_once' ni el 'use' porque Composer
// se encarga de la autocarga de la clase TCPDF.

class Pdf extends \TCPDF
{
    // El constructor padre se llama automáticamente.
    // Puedes añadir aquí métodos personalizados si los necesitas en el futuro.
}