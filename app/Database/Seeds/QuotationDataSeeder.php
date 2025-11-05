<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;
use App\Models\QuotationModel;

class QuotationDataSeeder extends Seeder
{
    public function run()
    {
        $model = new QuotationModel();
        
        $this->db->query('SET FOREIGN_KEY_CHECKS=0;');
        $model->truncate(); // Vacía completamente la tabla 'cotizaciones'
        $this->db->query('SET FOREIGN_KEY_CHECKS=1;');
        
        // Datos de prueba para el menú (simulación de detalle_menu JSON)
        $menuDetail = json_encode([
            "selection" => [
                "14" => ["15" => "17", "22" => "24"],
                "129" => "129"
            ],
            "quantities" => [
                "14" => "30",
                "129" => "30"
            ]
        ]);

        $cotizaciones = [];
        $now = time();
        
        // =================================================================
        // DATOS DE PRUEBA
        // =================================================================
        $nombres = ['Alejandro', 'Sofía', 'Ricardo', 'Valeria', 'Javier', 'Mariana', 'Luis', 'Andrea'];
        $apellidos = ['García', 'López', 'Martínez', 'Hernández', 'Rodríguez', 'Pérez', 'Sánchez'];
        $empresas = ['Innovatech Solutions', 'Grupo Alfa', 'Consultoría Zenith', 'Marketing Digital Pro', 'Desarrollos Urbanos'];
        
        $canalOptions = ['recomendacion', 'redes', 'restaurante', 'otro']; 
        $eventoOptions = ['social', 'empresarial', 'otro'];
        $consumidoresOptions = ['hombres', 'mujeres', 'ninos', 'mixto'];

        $modalidadOptions = ['buffet_asistido', 'buffet_self_service', 'servicio_a_la_mesa'];

        // =================================================================
        // BLOQUE 1: FORZAR DATOS PARA LA GRÁFICA DE 6 MESES (CONFIRMADOS)
        // =================================================================
        for ($m = 0; $m < 6; $m++) {
            $createdDate = date('Y-m-01 H:i:s', strtotime("-$m months"));
            $eventDate = date('Y-m-d', strtotime("+1 month", strtotime($createdDate)));
            
            $totalEstimado = rand(15000, 45000) + (rand(0, 99) / 100);
            $tipoEvento = ($m % 2 == 0) ? 'empresarial' : 'social'; // Alternar para variar la gráfica
            $nombreCompleto = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];

            $cotizaciones[] = [
                'cliente_nombre'    => $nombreCompleto,
                'cliente_whatsapp'  => '+52155' . rand(10000000, 99999999),
                'num_invitados'     => rand(50, 150),
                'fecha_evento'      => $eventDate,
                'total_estimado'    => $totalEstimado, 
                'status'            => 'confirmado',
                
                // Campos de formulario (valores de prueba)
                'tipo_evento'       => $tipoEvento,
                'nombre_empresa'    => ($tipoEvento === 'empresarial') ? $empresas[array_rand($empresas)] : null,
                'hora_inicio'       => '18:00:00',
                'hora_consumo'      => '19:30:00',
                'hora_finalizacion' => '22:00:00',
                'direccion_evento'  => 'Av. Reforma 100, Col. Juárez, CDMX',
                'mesa_mantel'       => 'si',
                'mesa_mantel_especificar' => null,
                'dificultad_montaje'=> 'Acceso fácil.',
                'como_nos_conocio'  => $canalOptions[array_rand($canalOptions)],
                'tipo_consumidores' => 'mixto',
                'restricciones_alimenticias' => null,
                'rango_presupuesto' => '$15,000 - $45,000',
                'detalle_menu'      => $menuDetail, 
                'notas_adicionales' => 'Evento de fin de año de la empresa.',
                'created_at'        => $createdDate,
                'updated_at'        => $createdDate,
                'modalidad_servicio'=> $modalidadOptions[array_rand($modalidadOptions)],
            ];
        }
        
        // --- BLOQUE 2: 15 Cotizaciones Aleatorias (para variedad en la tabla) ---
        $statusOptions = ['pendiente', 'confirmado', 'cancelado', 'pagado', 'contactado', 'en_revision'];
        
        for ($i = 0; $i < 15; $i++) {
            $createdTimestamp = strtotime("-".rand(0, 180)." days", $now);
            $createdAt = date('Y-m-d H:i:s', $createdTimestamp);
            $eventTimestamp = strtotime("+".rand(10, 90)." days", $now);
            $fechaEvento = date('Y-m-d', $eventTimestamp);
            $totalEstimado = rand(5000, 35000) + (rand(0, 99) / 100);
            $tipoEvento = $eventoOptions[array_rand($eventoOptions)];
            $nombreCompleto = $nombres[array_rand($nombres)] . ' ' . $apellidos[array_rand($apellidos)];

            $cotizaciones[] = [
                'cliente_nombre'    => $nombreCompleto,
                'cliente_whatsapp'  => '+52155' . rand(10000000, 99999999),
                'num_invitados'     => rand(30, 200),
                'fecha_evento'      => $fechaEvento,
                'total_estimado'    => $totalEstimado,
                'status'            => $statusOptions[array_rand($statusOptions)],
                
                'tipo_evento'       => $tipoEvento,
                'nombre_empresa'    => ($tipoEvento === 'empresarial') ? $empresas[array_rand($empresas)] : null,
                'hora_inicio'       => date('H:i:s', strtotime(rand(10, 20) . ':00:00')),
                'hora_consumo'      => date('H:i:s', strtotime(rand(12, 21) . ':30:00')),
                'hora_finalizacion' => date('H:i:s', strtotime(rand(20, 23) . ':00:00')),
                'direccion_evento'  => 'Calle Falsa ' . rand(1, 999) . ', Col. Test, CDMX',
                'mesa_mantel'       => ($i % 2 == 0) ? 'si' : 'no',
                'mesa_mantel_especificar' => ($i % 4 == 0) ? 'Solo manteles negros' : null,
                'dificultad_montaje'=> ($i % 3 == 0) ? '5to piso sin elevador.' : 'Acceso fácil.',
                'como_nos_conocio'  => $canalOptions[array_rand($canalOptions)],
                'tipo_consumidores' => $consumidoresOptions[array_rand($consumidoresOptions)],
                'restricciones_alimenticias' => ($i % 5 == 0) ? 'Vegetarianos' : null,
                'rango_presupuesto' => '$10,000 - $20,000',
                'modalidad_servicio'=> $modalidadOptions[array_rand($modalidadOptions)],
                
                'detalle_menu'      => $menuDetail, 
                'notas_adicionales' => 'Cotización estándar para evento social.',
                'created_at'        => $createdAt,
                'updated_at'        => $createdAt,
            ];
        }

        // Insertar los datos en la base de datos
        $model->insertBatch($cotizaciones);
    }
}